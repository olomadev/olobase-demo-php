<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Filter\Auth\AuthFilter;
use Oloma\Php\Error\ErrorWrapperInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $cache = $container->get(StorageInterface::class);
        $auth = $container->get(AuthenticationInterface::class);
        $error = $container->get(ErrorWrapperInterface::class);
        $filter = $container->get(InputFilterPluginManager::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(AuthFilter::class);

        return new TokenHandler(
            $container->get('config'),
            $cache,
            $auth,
            $inputFilter,
            $error
        );
    }
}