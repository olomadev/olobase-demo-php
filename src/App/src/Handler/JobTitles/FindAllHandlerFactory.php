<?php

declare(strict_types=1);

namespace App\Handler\JobTitles;

use App\Model\JobTitleModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $jobTitleModel = $container->get(JobTitleModel::class);
        return new FindAllHandler($jobTitleModel);
    }
}
