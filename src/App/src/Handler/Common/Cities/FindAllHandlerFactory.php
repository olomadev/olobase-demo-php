<?php

declare(strict_types=1);

namespace App\Handler\Common\Cities;

use App\Model\CommonModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $commonModel = $container->get(CommonModel::class);
        return new FindAllHandler($commonModel);
    }
}
