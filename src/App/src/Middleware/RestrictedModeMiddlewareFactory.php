<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class RestrictedModeMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RestrictedModeMiddleware(
            $container->get(Translator::class)
        );
    }
}