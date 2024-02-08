<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use App\Utils\TokenEncrypt;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\Authentication\JwtEncoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

use function getRealUserIp;

class TokenModel
{
    private $conn;
    private $users;
    private $cache;
    private $config;
    private $encoder;
    private $tokenEncrypt;

    public function __construct(
        array $config,
        StorageInterface $cache,
        TokenEncrypt $tokenEncrypt,
        JwtEncoderInterface $encoder,
        TableGatewayInterface $users
    )
    {
        $this->users = $users;
        $this->cache = $cache;
        $this->config = $config;
        $this->encoder = $encoder;
        $this->tokenEncrypt = $tokenEncrypt;
        $this->conn = $users->getAdapter()
            ->getDriver()
            ->getConnection();
        
        $sessionTTL = $this->config['token']['session_ttl'] * 60;
        if ($sessionTTL < 10) {
            throw new Exception("Configuration error: Session ttl value cannot be less than 10 minutes.");
        }
    }
    
    /**
     * Decode token
     * 
     * @param  string $token token
     * @return mixed
     */
    public function decode(string $token)
    {
        return $this->encoder->decode($token);
    }
    
    /**
     * Generate token header variables
     * 
     * @param  ServerRequestInterface $request psr7 http request object
     * @return array
     */
    private function generateHeader(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $mtRand     = mt_rand();
        $tokenId    = md5(uniqid((string)$mtRand, true));
        $issuedAt   = time();
        $notBefore  = $issuedAt;
        $expire     = $notBefore + (60 * $this->config['token']['token_validity']);
        $http       = empty($server['HTTPS']) ? 'http://' : 'https://';
        $issuer     = $http.$server['HTTP_HOST'];
        $userAgent  = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $deviceKey  = md5($userAgent);
        return [
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer,
            $deviceKey
        ];
    }

    /**
     * Returns to encoded token with expire date
     *
     * @param  ServerRequestInterface $request request
     * @return array|boolean
     */
    public function create(ServerRequestInterface $request)
    {
        $user   = $request->getAttribute(UserInterface::class);
        $userId = $user->getId();
        //
        // JWT header
        //
        list(
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer
        ) = $this->generateHeader($request);
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
                'userId' => $userId,     // userid from the users table
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
        //
        // update last login date
        // 
        try {
            $this->conn->beginTransaction();
            $this->users->update(['lastLogin' => date("Y-m-d H:i:s", $issuedAt)], ['userId' => $userId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
        //
        // create token session
        //
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
        $this->cache->getOptions()->setTtl($configSessionTTL);
        $this->cache->setItem(SESSION_KEY.$userId.":".$tokenId, $configSessionTTL);

        return [
            'token' => $this->tokenEncrypt->encrypt($token),
            'tokenId' => $tokenId,
            'expiresAt' => date('Y-m-d H:i:s', $expire),
        ];
    }

    /**
     * Refresh token
     * 
     * @param  ServerRequestInterface $request request
     * @param  array                  $decoded payload
     * @return array|boolean
     */
    public function refresh(ServerRequestInterface $request, array $decoded)
    {
        $server = $request->getServerParams();
        $userAgent = empty($server['HTTP_USER_AGENT']) ? 'unknown' : $server['HTTP_USER_AGENT'];
        $userId = $decoded['data']['userId'];
        //
        // validate token session
        //
        $oldTokenId = $decoded['data']['details']['tokenId'];
        $sessionTTL = $this->cache->getItem(SESSION_KEY.$userId.":".$oldTokenId);
        if (! $sessionTTL) {
            return false; // ttl expired
        }
        $expiredAt = $decoded['exp'];
        $now = time();
        if ($expiredAt + (int)$sessionTTL + 10 < $now) {
            return false; // ttl expired
        }  
        //
        // JWT header - renew token
        // 
        list(
            $tokenId,
            $issuedAt,
            $notBefore,
            $expire,
            $issuer,
            $deviceKey
        ) = $this->generateHeader($request);
        //
        // Renew JWT token data
        //
        $decoded['data']['details']['tokenId'] = $tokenId; // renew token id
        $decoded['data']['details']['ip'] = getRealUserIp(); 
        $decoded['data']['details']['deviceKey'] = $deviceKey; 
        $jwt = [
            'iat'  => $decoded['iat'],  // Issued at: time when the token was generated
            'jti'  => $tokenId,         // Json Token Id: an unique identifier for the token
            'iss'  => $decoded['iss'],  // Issuer
            'nbf'  => $notBefore,       // Not before
            'exp'  => $expire,          // Expire
            'data' => (array)$decoded['data']
        ];
        $newToken = $this->encoder->encode($jwt);
        //
        // refresh the user session
        //
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
        $this->cache->getOptions()->setTtl($configSessionTTL);
        $this->cache->setItem(SESSION_KEY.$userId.":".$tokenId, $configSessionTTL);
        $this->cache->removeItem(SESSION_KEY.$userId.":".$oldTokenId);

        return [
            'token' => $this->tokenEncrypt->encrypt($newToken),
            'tokenId' => $tokenId,
            'expiresAt' => date("Y-m-d H:i:s", $expire),
            'data' => (array)$decoded['data']
        ];
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
        $this->cache->removeItem(SESSION_KEY.$userId.":".$tokenId);
    }
    
    /**
     * Returns to token encryption object
     * 
     * @return object
     */
    public function getTokenEncrypt() : TokenEncrypt
    {
        return $this->tokenEncrypt;
    }
}
