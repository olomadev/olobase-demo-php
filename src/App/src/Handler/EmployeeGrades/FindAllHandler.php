<?php

declare(strict_types=1);

namespace App\Handler\EmployeeGrades;

use App\Model\EmployeeGradeModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(EmployeeGradeModel $employeeGradeModel)
    {
        $this->employeeGradeModel = $employeeGradeModel;
    }

    /**
     * @OA\Get(
     *   path="/employeegrades/findAll",
     *   tags={"Permissions"},
     *   summary="Find all employee grades",
     *   operationId="employeeGrades_findAll",
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
        $data = $this->employeeGradeModel->findEmployeeGrades();
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
