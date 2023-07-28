<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Model\JobTitleListModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(JobTitleListModel $jobTitleListModel)
    {
        $this->jobTitleListModel = $jobTitleListModel;
    }

    /**
     * @OA\Get(
     *   path="/jobtitlelists/findAll",
     *   tags={"JobTitle Lists"},
     *   summary="Find all jobTitles",
     *   operationId="jobTitleLists_findAll",
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
        $data = $this->jobTitleListModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
