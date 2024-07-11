<?php

declare(strict_types=1);

namespace App\Handler\FailedLogins;

use App\Model\FailedLoginModel;
use App\Filter\FailedLogins\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $failedLoginModel = $container->get(FailedLoginModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($failedLoginModel, $inputFilter, $error);
    }
}
