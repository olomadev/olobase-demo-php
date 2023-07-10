<?php

declare(strict_types=1);

namespace App\Container;

use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class PredisFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['redis'];

        return new PredisClient(
            [
                "scheme" => "tcp",
                "host" => $config['host'],
                "port" => $config['port'],
                "password" => $config['password'],
                "timeout" => $config['timeout'],
                // "persistent" => "1"
            ]
        );
    }
}
