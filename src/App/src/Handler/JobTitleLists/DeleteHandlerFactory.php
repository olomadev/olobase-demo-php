<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Model\JobTitleListModel;
use App\Filter\JobTitleLists\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DeleteHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $jobTitleListModel = $container->get(JobTitleListModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DeleteFilter::class);

        return new DeleteHandler($jobTitleListModel, $inputFilter, $error);
    }
}
