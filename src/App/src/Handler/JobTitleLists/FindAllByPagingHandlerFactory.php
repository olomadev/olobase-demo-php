<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Model\JobTitleListModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $JobTitleListModel = $container->get(JobTitleListModel::class);
        return new FindAllByPagingHandler($JobTitleListModel);
    }
}
