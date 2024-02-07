<?php

declare(strict_types=1);

namespace App\Handler\Departments;

use App\Model\DepartmentModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(DepartmentModel $departmentModel)
    {
        $this->departmentModel = $departmentModel;
    }

    /**
     * @OA\Get(
     *   path="/departments/findAll",
     *   tags={"Permissions"},
     *   summary="Find all employee grades",
     *   operationId="departments_findAll",
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
        $params = $request->getQueryParams();
        $companyId = empty($params['companyId']) ? null : $params['companyId'];
        $data = $this->departmentModel->findDepartments($companyId);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
