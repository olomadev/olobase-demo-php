<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Olobase\Mezzio\DataManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $employeeModel = $container->get(EmployeeModel::class);
        $dataManager = $container->get(DataManagerInterface::class);
        return new FindOneByIdHandler($employeeModel, $dataManager);
    }
}
