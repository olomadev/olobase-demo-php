<?php

declare(strict_types=1);

namespace App\Container;

use App\Utils\CampaignManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CampaignManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
    	return new CampaignManager;
    }
}
