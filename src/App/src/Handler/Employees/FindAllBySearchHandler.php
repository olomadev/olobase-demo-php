<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllBySearchHandler implements RequestHandlerInterface
{
    public function __construct(EmployeeModel $employeeModel)
    {
        $this->employeeModel = $employeeModel;
    }
    
    /**
     * @OA\Get(
     *   path="/employees/findAllBySearch",
     *   tags={"Employees"},
     *   summary="Search for all employees",
     *   operationId="employees_findAllBySearch",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeFindAllBySearch"),
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
        $data = $this->employeeModel->findAllBySearch($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
