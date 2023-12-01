<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\SmtpMailer;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class SmtpMailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SmtpMailer(
            $container->get('config'), 
            $container->get(TranslatorInterface::class)
        );
    }
}
