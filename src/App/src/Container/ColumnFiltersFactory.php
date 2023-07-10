<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\ColumnFilters;
use Laminas\Db\Adapter\AdapterInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ColumnFiltersFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get(AdapterInterface::class);
        return new ColumnFilters($adapter);
    }
}
