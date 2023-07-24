<?php

declare(strict_types=1);

namespace App\Handler\JobTitles;

use App\Model\JobTitleModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllByPagingHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $JobTitleModel = $container->get(JobTitleModel::class);
        return new FindAllByPagingHandler($JobTitleModel);
    }
}
