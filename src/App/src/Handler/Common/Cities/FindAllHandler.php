<?php

declare(strict_types=1);

namespace App\Handler\Common\Cities;

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
     *   path="/cities/findAll",
     *   tags={"Common"},
     *   summary="Find all cities",
     *   operationId="cities_findAll",
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
        if (empty($get['countryId'])) {
            return new JsonResponse([
                'error' => "Country ID cannot be empty"
            ]);    
        }
        $data = $this->commonModel->findCitiesByCountryId($get['countryId']);
        return new JsonResponse([
            'data' => $data
        ]);
    }

}
