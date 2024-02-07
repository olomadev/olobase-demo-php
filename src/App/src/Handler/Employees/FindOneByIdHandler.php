<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Olobase\Mezzio\DataManagerInterface;
use App\Schema\Employees\EmployeesFindOneById;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(
        private EmployeeModel $employeeModel,
        private DataManagerInterface $dataManager
    )
    {
        $this->employeeModel = $employeeModel;
        $this->dataManager = $dataManager;
    }

    /**
     * @OA\Get(
     *   path="/employees/findOneById/{employeeId}",
     *   tags={"Employees"},
     *   summary="Find employee data",
     *   operationId="employees_findOneById",
     *
     *   @OA\Parameter(
     *       name="employeeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeesFindOneById"),
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $row = $this->employeeModel->findOneById($request->getAttribute("employeeId"));
        if ($row) {
            $data = $this->dataManager->getViewData(EmployeesFindOneById::class, $row);
            return new JsonResponse($data);   
        }
        return new JsonResponse([], 404);
    }

}
