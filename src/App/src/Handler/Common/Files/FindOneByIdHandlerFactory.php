<?php

declare(strict_types=1);

namespace App\Handler\Common\Files;

use App\Model\FileModel;
use App\Filter\Files\DownloadFilter;
use Psr\Container\ContainerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FindOneByIdHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $translator = $container->get(Translator::class);
        $fileModel = $container->get(FileModel::class);
        $error = $container->get(Error::class);

        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(DownloadFilter::class);

        return new FindOneByIdHandler($translator, $fileModel, $inputFilter, $error);
    }
}
