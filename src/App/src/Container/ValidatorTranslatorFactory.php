<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\ValidatorTranslator;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * This fixes a bug in the Laminas Validator Plugin Manager 
 * 
 * @see https://github.com/laminas/laminas-validator/issues/194
 */
class ValidatorTranslatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $validatorTranslator = new ValidatorTranslator($container->get(Translator::class));
        return $validatorTranslator;
    }
}

