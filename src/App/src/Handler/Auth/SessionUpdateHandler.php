<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class SessionUpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        array $config, 
        SimpleCacheInterface $simpleCache
    )
    {
        $this->config = $config;
        $this->simpleCache = $simpleCache;
    }

    /**
     * @OA\Get(
     *   path="/auth/session",
     *   tags={"Auth"},
     *   summary="Updaate session with aixos requests",
     *   operationId="auth_session",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation"
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        if ($user) {
            // Reset session ttl
            // 
            $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
            // 
            // reset session ttl using cache 
            // 
            $this->simpleCache->set(SESSION_KEY.$user->getId(), $configSessionTTL, $configSessionTTL);
            
            return new TextResponse("ok", 200);
        }
        return new TextResponse("logout", 200);
    }
}
