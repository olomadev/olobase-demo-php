<?php

declare(strict_types=1);

namespace App\Container;

use App\Authentication\JwtEncoder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class JwtEncoderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        return new JwtEncoder($config['jwt_encoder']['secret_key']);
    }
}
