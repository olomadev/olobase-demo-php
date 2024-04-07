<?php

declare(strict_types=1);

namespace App\Utils;

use Psr\Container\ContainerInterface;
use Predis\ClientInterface as Predis;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class SmtpMailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SmtpMailer(
            $container->get('config'),
            $container->get(Predis::class),
            $container->get(TranslatorInterface::class)
        );
    }
}
