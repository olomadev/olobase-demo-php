<?php

declare(strict_types=1);

namespace App\Handler\FailedLogins;

use App\Model\FailedLoginModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllUsernamesHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $failedLoginModel = $container->get(FailedLoginModel::class);
        return new FindAllUsernamesHandler($failedLoginModel);
    }
}
