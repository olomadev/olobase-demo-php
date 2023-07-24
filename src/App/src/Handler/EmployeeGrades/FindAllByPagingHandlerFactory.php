<?php

declare(strict_types=1);

namespace App\Handler\EmployeeGrades;

use App\Model\EmployeeGradeModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $roleModel = $container->get(EmployeeGradeModel::class);
        return new FindAllByPagingHandler($roleModel);
    }
}
