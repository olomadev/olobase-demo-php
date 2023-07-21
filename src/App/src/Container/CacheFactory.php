<?php

declare(strict_types=1);

namespace App\Container;

use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
/**
 * https://docs.laminas.dev/laminas-cache/storage/adapter/#quick-start
 * 
 * Max Memory Config
 * https://stackoverflow.com/questions/33115325/how-to-set-redis-max-memory/33119590
 */
class CacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $redis  = $config['redis'];
        /**
         * https://docs.laminas.dev/laminas-cache/storage/adapter/#the-redis-adapter
         */
        $storageFactory = $container->get(StorageAdapterFactoryInterface::class);
        $config = [
            'adapter' => 'redis',
            'options' => [
                'ttl' => 0, // 86400, // 24 saat, 3600 = 1 saat
                'namespace' => '',
                'server'  => [
                    'host' => $redis['host'],
                    'port' => $redis['port'],
                    'timeout' => $redis['timeout'],
                ],
                'password' => $redis['password'],
            ],
            'plugins' => [
                ['name' => 'serializer']
            ],
        ];
        $storage = $storageFactory->createFromArrayConfiguration($config);
        return $storage;
    }
}
