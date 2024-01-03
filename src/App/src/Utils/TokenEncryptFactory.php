<?php

declare(strict_types=1);

namespace App\Utils;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TokenEncryptFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TokenEncrypt(
            $container->get('config')
        );
    }
}
