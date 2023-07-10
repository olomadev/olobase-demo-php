<?php

declare(strict_types=1);

namespace App\Container;

use Laminas\Db\Adapter\AdapterInterface;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\AuthenticationInterface;
use App\Middleware\JwtAuthenticationDocumentMiddleware;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class JwtAuthenticationDocumentMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : JwtAuthenticationDocumentMiddleware
    {
        $authentication = $container->has(AuthenticationInterface::class) ? $container->get(AuthenticationInterface::class) : null;
        if (null === $authentication) {
            throw new Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }
        return new JwtAuthenticationDocumentMiddleware(
            $authentication,
            $container->get(AdapterInterface::class),
            $container->get(Translator::class)
        );
    }
}
