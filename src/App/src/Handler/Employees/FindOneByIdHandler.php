<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(EmployeeModel $employeeModel)
    {
        $this->employeeModel = $employeeModel;
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
            return new JsonResponse(
                [
                    'data' => (new FindOneByIdViewModel($row))->getData()
                ]
            );
        }
        return new JsonResponse([], 404);
    }

}
