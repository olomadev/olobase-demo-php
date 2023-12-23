<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Authentication\UserInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
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
    /**
     * @var AuthenticationInterface
     */
    protected $auth;
    protected $config;
    protected $translator;

    public function __construct(
        array $config, 
        SimpleCacheInterface $simpleCache,
        AuthenticationInterface $auth, 
        Translator $translator
    )
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->simpleCache = $simpleCache;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {        
        $configSessionTTL = (int)$this->config['token']['session_ttl'] * 60; // for strong security reason it should be less
        try {
            $user = $this->auth->authenticate($request);
            if (null !== $user) {
        
                // reset session ttl using cache 
                // 
                $this->simpleCache->set(SESSION_KEY.$user->getId(), $configSessionTTL, $configSessionTTL);
                //
                //
                return $handler->handle($request->withAttribute(UserInterface::class, $user));
            }
        } catch (ExpiredException $e) {

            // 401 Unauthorized response
            // Response Header = 'Token-Expired: true'

            return new JsonResponse(['data' => ['error' => $this->translator->translate('Token Expired')]], 401, ['Token-Expired' => 1]);
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
