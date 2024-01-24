<?php

declare(strict_types=1);

namespace App\Filter\JobTitleLists;

use App\Model\CommonModel;
use Psr\Container\ContainerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ImportFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ImportFilter(
            $container->get(CommonModel::class),
            $container->get(InputFilterPluginManager::class)
        );
    }
}
