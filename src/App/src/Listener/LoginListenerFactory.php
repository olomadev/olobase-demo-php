<?php

declare(strict_types=1);

namespace App\Listener;

use App\Model\FailedLoginModel;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LoginListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $failedLoginModel = $container->get(FailedLoginModel::class);
        return new LoginListener($failedLoginModel);
    }
}
