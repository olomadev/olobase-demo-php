<?php

declare(strict_types=1);

namespace App\Filter\Departments;

use App\Model\CommonModel;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class SaveFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SaveFilter(
            $container->get(CommonModel::class),
            $container->get(InputFilterPluginManager::class)
        );
    }
}
