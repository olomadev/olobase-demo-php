<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class RestrictedModeMiddleware implements MiddlewareInterface
{
    public function __construct(
        Translator $translator
    )
    {
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {        
        $currentPath =  $request->getUri()->getPath();
        $allowedRoutes = [
            "/api/auth/token",
            "/api/auth/session",
            "/api/auth/refresh",
            "/api/auth/logout",
        ];
        if (! in_array($currentPath, $allowedRoutes) 
            && in_array($request->getMethod(), ["POST", "PUT", "DELETE"])
        ) {
            return new JsonResponse(
                [
                    'data' => [
                        'error' => $this->translator->translate(
                            "Demo mode can only handle read operations. To perform writes, install the demo application in your environment and remove the RestrictedModeMiddleware class from config/pipeline.php"
                        )
                    ]
                ],
                400
            );
        }
        return $handler->handle($request);
    }
}
