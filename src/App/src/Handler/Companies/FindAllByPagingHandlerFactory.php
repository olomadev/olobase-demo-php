<?php

declare(strict_types=1);

namespace App\Handler\Companies;

use App\Model\CompanyModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $companyModel = $container->get(CompanyModel::class);
        return new FindAllByPagingHandler($companyModel);
    }
}
