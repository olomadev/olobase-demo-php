<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Authentication\UserInterface;
use Laminas\Cache\Storage\StorageInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Firebase\JWT\ExpiredException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class JwtAuthenticationMiddleware implements MiddlewareInterface
{
    protected $auth;
    protected $config;
    protected $translator;

    public function __construct(
        array $config, 
        StorageInterface $cache,
        AuthenticationInterface $auth, 
        Translator $translator
    )
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->cache = $cache;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {        
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60;
        try {
            $user = $this->auth->authenticate($request);

            if (null !== $user) {
                //
                // reset session ttl using cache 
                // 
                $tokenId = $user->getDetails()['tokenId'];
                $this->cache->getOptions()->setTtl($configSessionTTL);
                $userHasSession = $this->cache->getItem(SESSION_KEY.$user->getId().":".$tokenId);
                if ($userHasSession) {
                    $this->cache->setItem(SESSION_KEY.$user->getId().":".$tokenId, $configSessionTTL);    
                }
                return $handler->handle($request->withAttribute(UserInterface::class, $user));
            }
        } catch (ExpiredException $e) { // 401 Unauthorized response
            return new JsonResponse(
                [
                    'data' => [
                        'error' => $this->translator->translate('Token Expired')]
                    ],
                    401,
                    [
                        'Token-Expired' => 1
                    ]
            );
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
