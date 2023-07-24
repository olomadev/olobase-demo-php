<?php

declare(strict_types=1);

namespace App\Handler\EmployeeGrades;

use App\Model\EmployeeGradeModel;
use App\Filter\EmployeeGrades\SaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UpdateHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $employeeGradeModel = $container->get(EmployeeGradeModel::class);
        $error = $container->get(Error::class);
        $dataManager = $container->get(DataManagerInterface::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(SaveFilter::class);

        return new UpdateHandler($employeeGradeModel, $dataManager, $inputFilter, $error);
    }
}
