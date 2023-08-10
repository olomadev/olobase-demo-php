<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(Translator::class);
        $employeeModel = $container->get(EmployeeModel::class);
        return new FindAllHandler($translator, $employeeModel);
    }
}
