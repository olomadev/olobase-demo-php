<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\Mailer;
use App\Utils\ErrorMailer;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class ErrorMailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
    	$dbAdapter = $container->get(AdapterInterface::class);
        $errors = new TableGateway('errors', $dbAdapter, null);
    	return new ErrorMailer(
    		$container->get(Mailer::class),
    		$errors
    	);
    }
}
