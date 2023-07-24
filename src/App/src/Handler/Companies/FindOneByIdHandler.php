<?php

declare(strict_types=1);

namespace App\Handler\Companies;

use App\Model\CompanyModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(CompanyModel $companyModel)
    {
        $this->companyModel = $companyModel;
    }

    /**
     * @OA\Get(
     *   path="/companies/findOneById/{companyId}",
     *   tags={"Companies"},
     *   summary="Find company data",
     *   operationId="companies_findOneById",
     *
     *   @OA\Parameter(
     *       name="companyId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CompanyFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $companyId = $request->getAttribute("companyId");
        $row = $this->companyModel->findOneById($companyId);
        if ($row) {
            $viewModel = new FindOneByIdViewModel($row);
            return new JsonResponse(['data' => $viewModel->getData()]);
        }
        return new JsonResponse([], 404);
    }

}
