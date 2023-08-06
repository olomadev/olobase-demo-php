<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Mezzio\Authentication\UserInterface;
use Oloma\Php\Authentication\JwtEncoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class TokenModel
{
    private $users;
    private $encoder;
    private $refreshToken;

    public function __construct(
        array $config,
        JwtEncoderInterface $encoder,
        TableGatewayInterface $users,
        TableGatewayInterface $refreshToken
    )
    {
        $this->users = $users;
        $this->config = $config;
        $this->encoder = $encoder;
        $this->refreshToken = $refreshToken;
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
        $expire     = $notBefore + (60 * $config['access_token_duration_in_minutes']);  // Adding 60 minute
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
                    'firstname' => $user->getDetail('firstname'),
                    'lastname' => $user->getDetail('lastname'),        
                    'ip' => $user->getDetail('ip'),
                    'deviceKey' => $user->getDetail('deviceKey'),
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
            // 'ref_channel' => 'web', // $user->getDetail('ref_channel'),
            'issuer' => $issuer,
            'ip' => $user->getDetail('ip'),
            'userAgent' => substr($userAgent, 0, 255),
            'deviceKey' => $user->getDetail('deviceKey'),
            'createdAt' => $createdAt,
            'expiresAt' => $expiresAt,
        );
        $this->refreshToken->insert($data);
        $this->users->update(['lastLogin' => $createdAt], ['userId' => $user->getId()]);

        return ['token' => $token, 'expiresAt' => $expiresAt, 'avatar' => $user->getDetail('avatar')];
    }

    public function refresh(ServerRequestInterface $request, string $tokenId, array $decoded)
    {
        $post = $request->getParsedBody();
        $config = $this->config['token'];

        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $deviceKey = md5($userAgent);
        $userId = $decoded['data']['userId'];

        $adapter = $this->refreshToken->getAdapter();
        $statement = $adapter->createStatement('SELECT updateCount FROM refreshTokens WHERE tokenId = ?');
        $resultSet = $statement->execute([$tokenId]);
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        if ($row) {

            // max usage sınırı geçildiyse 401 unauthorized response dön logout için
            //
            if ($config['refresh_token_max_usage'] > 0 AND $row['updateCount'] > $config['refresh_token_max_usage']) {
                return false;
            }
            //------------- Create New Token ------------------//

            $issuedAt   = time();
            $notBefore  = $issuedAt;   // Do not add seconds
            $expire     = $notBefore + (60 * $config['access_token_duration_in_minutes']);    // Adding 60 minute
            $jwt = [
                'iat'  => $decoded['iat'],  // Issued at: time when the token was generated
                'jti'  => $decoded['jti'],  // Json Token Id: an unique identifier for the token
                'iss'  => $decoded['iss'],  // Issuer
                'nbf'  => $notBefore,       // Not before
                'exp'  => $expire,          // Expire
                'data' => (array)$decoded['data']
            ];
            $newToken = $this->encoder->encode($jwt);

            //------------- Update Db ------------------//

            $updatedAt = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', $expire);
            $data = array(
                'updatedAt' => $updatedAt,
                'expiresAt' => $expiresAt,
                'updateCount' => $row['updateCount'] + 1,
            );
            $this->refreshToken->update($data, ['tokenId' => $tokenId]);

            //------------- Update Token ------------------//
        
            return ['token' => $newToken, 'expiresAt' => $expiresAt, 'data' => (array)$decoded['data']];
        }
        return false;
    }

    /**
     * kill the token for logout operations
     */
    public function kill($userId, $deviceKey)
    {
        $this->refreshToken->delete(['userId' => $userId, 'deviceKey' => $deviceKey]);
    }


}
