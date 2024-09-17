<?php

declare(strict_types=1);

namespace App\Handler\Common\Months;

use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private CommonModel $commonModel)
    {
        $this->commonModel = $commonModel;
    }

    /**
     * @OA\Get(
     *   path="/months/findAll",
     *   tags={"Common"},
     *   summary="Find all months",
     *   operationId="months_findAll",
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
        $data = $this->commonModel->findMonths();
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
