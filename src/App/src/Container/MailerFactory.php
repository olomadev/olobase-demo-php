<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\Mailer;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class MailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
    	return new Mailer($container->get(TranslatorInterface::class));
    }
}
