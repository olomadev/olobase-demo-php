<?php

declare(strict_types=1);

namespace App\Handler\Common\Years;

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
     *   path="/years/findAll",
     *   tags={"Common"},
     *   summary="Find all years",
     *   operationId="years_findAll",
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
        $data = $this->commonModel->findYears();
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
