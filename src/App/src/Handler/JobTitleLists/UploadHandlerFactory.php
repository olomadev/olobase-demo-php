<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Filter\JobTitleLists\FileUploadFilter;
use Predis\ClientInterface as Predis;
use Olobase\Mezzio\DataManagerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class UploadHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $predis = $container->get(Predis::class);
        $error = $container->get(Error::class);
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(FileUploadFilter::class);

        return new UploadHandler($predis, $inputFilter, $error);
    }
}