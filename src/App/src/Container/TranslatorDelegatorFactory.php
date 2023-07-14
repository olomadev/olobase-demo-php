<?php

declare(strict_types=1);

namespace App\Container;

use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\Resources;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

class TranslatorDelegatorFactory implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $translator = $callback();
                
        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        );
        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        );

        return $translator;
    }
}
