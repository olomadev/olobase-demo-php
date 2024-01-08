<?php

declare(strict_types=1);

namespace App\Handler\EmployeeGrades;

use App\Model\EmployeeGradeModel;
use App\Filter\EmployeeGrades\DeleteFilter;
use Oloma\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $employeeGradeModel = $container->get(EmployeeGradeModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($employeeGradeModel, $inputFilter, $error);
    }
}
