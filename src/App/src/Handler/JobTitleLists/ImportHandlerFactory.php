<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Predis\ClientInterface as Predis;
use Laminas\Cache\Storage\StorageInterface;
use App\Filter\JobTitleLists\ImportFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ImportHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(Translator::class);
        $error = $container->get(Error::class);
        $predis = $container->get(Predis::class);
        $storage = $container->get(StorageInterface::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(ImportFilter::class);

        return new ImportHandler($translator, $predis, $storage, $inputFilter, $error);
    }
}
