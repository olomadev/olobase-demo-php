<?php

declare(strict_types=1);

namespace App\Filter\Users;

use Psr\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PasswordSaveFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PasswordSaveFilter($container->get(AdapterInterface::class));
    }
}
