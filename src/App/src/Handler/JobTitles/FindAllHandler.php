<?php

declare(strict_types=1);

namespace App\Handler\JobTitles;

use App\Model\JobTitleModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(JobTitleModel $jobTitleModel)
    {
        $this->jobTitleModel = $jobTitleModel;
    }

    /**
     * @OA\Get(
     *   path="/jobTitles/findAll",
     *   tags={"Common"},
     *   summary="Find all job titles",
     *   operationId="jobTitles_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->jobTitleModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
