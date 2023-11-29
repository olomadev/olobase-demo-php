<?php

declare(strict_types=1);

namespace App\Container;

use Laminas\EventManager\EventManager;
use Psr\Container\ContainerInterface;

class EventManagerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new EventManager;
    }
}
