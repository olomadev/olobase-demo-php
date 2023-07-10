<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\EventManager;
use Psr\Container\ContainerInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;

class EventManagerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $appointmentHistory = new TableGateway(
        	'appointment_history',
        	$container->get(AdapterInterface::class),
        	null,
        	new ResultSet(ResultSet::TYPE_ARRAY)
        );
        return new EventManager($appointmentHistory);
    }
}
