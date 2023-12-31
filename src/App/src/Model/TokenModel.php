<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Authentication\UserInterface;
use Oloma\Php\Authentication\JwtEncoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class TokenModel
{
    private $conn;
    private $users;
    private $cache;
    private $config;
    private $encoder;
    private $tokens;

    public function __construct(
        array $config,
        StorageInterface $cache,
        JwtEncoderInterface $encoder,
        TableGatewayInterface $users,
        TableGatewayInterface $tokens
    )
    {
        $this->users = $users;
        $this->cache = $cache;
        $this->config = $config;
        $this->encoder = $encoder;
        $this->tokens = $tokens;
        $this->conn = $users->getAdapter()
            ->getDriver()
            ->getConnection();
        
        $sessionTTL = $this->config['token']['session_ttl'] * 60;
        if ($sessionTTL < 10) {
            throw new Exception("Configuration error: Session ttl value cannot be less than 10 minutes.");
        }
    }
    
    /**
     * Returns to encoded token with expire date
     *
     * @param  ServerRequestInterface $request request
     * @return array
     */
    public function create(ServerRequestInterface $request)
    {
        $user   = $request->getAttribute(UserInterface::class);
        $server = $request->getServerParams();

        $post = $request->getParsedBody();
        $config = $this->config['token'];

        $mtRand     = mt_rand();
        $tokenId    = md5(uniqid((string)$mtRand, true));
        $issuedAt   = time();
        // $notBefore  = $issuedAt + 10;           // Adding 10 seconds
        $notBefore  = $issuedAt; // node.js nJwt token çalışmyor extra time ekler isek
        $expire     = $notBefore + (60 * $config['token_validity']);
        $http       = empty($server['HTTPS']) ? 'http://' : 'https://';
        $issuer     = $http.$server['HTTP_HOST'];
        $userAgent  = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        //
        // JWT token data
        //
        $jwt = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $issuer,           // Issuer
            'nbf'  => $notBefore,        // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'userId' => $user->getId(),     // userid from the users table
                'identity' => $user->getIdentity(), // User identity can be email, username or phone
                'roles' => $user->getRoles(),
                'details' => [
                    'email' => $user->getDetail('email') ? $user->getDetail('email') : $user->getIdentity(), // User email
                    'fullname' => $user->getDetail('fullname'),
                    'ip' => $user->getDetail('ip'),
                    'deviceKey' => $user->getDetail('deviceKey'),
                    'tokenId' => $tokenId,
                ],
            ]
        ];
        $token = $this->encoder->encode($jwt);

        //------------- Insert Token ------------------//

        $createdAt = date('Y-m-d H:i:s', $issuedAt);
        $expiresAt = date('Y-m-d H:i:s', $expire);
        $data = array(
            'tokenId' => $tokenId,
            'userId' => $user->getId(),
            'issuer' => $issuer,
            'ip' => $user->getDetail('ip'),
            'userAgent' => substr($userAgent, 0, 255),
            'deviceKey' => $user->getDetail('deviceKey'),
            'createdAt' => $createdAt,
            'expiresAt' => $expiresAt,
        );

        try {
            $this->conn->beginTransaction();
            $this->tokens->insert($data);
            $this->users->update(['lastLogin' => $createdAt], ['userId' => $user->getId()]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        return ['token' => $token, 'tokenId' => $tokenId, 'expiresAt' => $expiresAt, 'avatar' => $user->getDetail('avatar')];
    }


    public function refresh(ServerRequestInterface $request, array $decoded)
    {
        $post = $request->getParsedBody();
        $config = $this->config['token'];

        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $userId = $decoded['data']['userId'];

        //------------- Create New Token ------------------//

        $mtRand     = mt_rand();
        $issuedAt   = time();
        $tokenId    = md5(uniqid((string)$mtRand, true));
        $notBefore  = $issuedAt;   // Do not add seconds
        $expire     = $notBefore + (60 * $config['token_validity']);    // Adding 60 minute
        $http       = empty($server['HTTPS']) ? 'http://' : 'https://';
        $issuer     = $http.$server['HTTP_HOST'];

        $decoded['data']['details']['tokenId'] = $tokenId;
        $jwt = [
            'iat'  => $decoded['iat'],  // Issued at: time when the token was generated
            'jti'  => $tokenId,         // Json Token Id: an unique identifier for the token
            'iss'  => $decoded['iss'],  // Issuer
            'nbf'  => $notBefore,       // Not before
            'exp'  => $expire,          // Expire
            'data' => (array)$decoded['data']
        ];
        $newToken = $this->encoder->encode($jwt);

        //------------- Insert Token ------------------//

        $createdAt = date('Y-m-d H:i:s', $issuedAt);
        $expiresAt = date('Y-m-d H:i:s', $expire);
        $data = array(
            'tokenId' => $tokenId,
            'userId' => $userId,
            'issuer' => $issuer,
            'ip' => $decoded['data']['details']['ip'],
            'userAgent' => substr($userAgent, 0, 255),
            'deviceKey' => $decoded['data']['details']['deviceKey'],
            'createdAt' => $createdAt,
            'expiresAt' => $expiresAt,
        );
        
        try {
            $this->conn->beginTransaction();
            $this->tokens->insert($data);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        //------------- Insert Token ------------------//

        return ['token' => $newToken, 'expiresAt' => $expiresAt, 'data' => (array)$decoded['data']];
    }

    /**
     * Kill current token for logout operation
     * 
     * @param  string $userId  user id
     * @param  string $tokenId token id
     * @return void
     */
    public function kill(string $userId, string $tokenId)
    {
        try {
            $this->conn->beginTransaction();
            $this->cache->removeItem(SESSION_KEY.$userId.":".$tokenId);
            $this->tokens->delete(['userId' => $userId, 'tokenId' => $tokenId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
