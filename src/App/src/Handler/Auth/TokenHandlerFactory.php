<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Filter\Auth\AuthFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class TokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $auth = $container->get(AuthenticationInterface::class);
        $error = $container->get(ErrorWrapperInterface::class);
        
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(AuthFilter::class);

        return new TokenHandler(
            $container->get('config'),
            $auth,
            $inputFilter,
            $error
        );
    }
}