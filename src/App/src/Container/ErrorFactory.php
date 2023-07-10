<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\Error;
use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ErrorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
    	return new Error($container->get(TranslatorInterface::class));
    }
}
