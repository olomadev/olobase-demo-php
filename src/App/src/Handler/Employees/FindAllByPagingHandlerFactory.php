<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $employeeModel = $container->get(EmployeeModel::class);
        return new FindAllByPagingHandler($employeeModel);
    }
}
