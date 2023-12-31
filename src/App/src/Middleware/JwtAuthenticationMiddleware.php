<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Authentication\UserInterface;
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
     * This signal is controlled by the frontend, do not change the value.
     */
    protected const EXPIRE_SIGNAL = 'Token Expired';

    protected $auth;
    protected $config;
    protected $translator;

    public function __construct(
        array $config,
        AuthenticationInterface $auth, 
        Translator $translator
    )
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {        
        try {
            $user = $this->auth->authenticate($request);

            if (null !== $user) {
                return $handler->handle($request->withAttribute(UserInterface::class, $user));
            }
        } catch (ExpiredException $e) { // 401 Unauthorized response
            return new JsonResponse(
                [
                    'data' => [
                        'error' => Self::EXPIRE_SIGNAL]
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
