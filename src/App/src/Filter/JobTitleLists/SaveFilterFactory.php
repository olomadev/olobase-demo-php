<?php

declare(strict_types=1);

namespace App\Filter\JobTitleLists;

use App\Model\CommonModel;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SaveFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SaveFilter($container->get(CommonModel::class));
    }
}
