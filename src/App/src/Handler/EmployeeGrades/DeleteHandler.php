<?php

declare(strict_types=1);

namespace App\Handler\EmployeeGrades;

use App\Model\EmployeeGradeModel;
use App\Filter\EmployeeGrades\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private EmployeeGradeModel $employeeGradeModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->employeeGradeModel = $employeeGradeModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/employeegrades/delete/{gradeId}",
     *   tags={"Employee Grades"},
     *   summary="Delete employee grade",
     *   operationId="employeeGrades_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="gradeId",
     *       required=true,
     *       description="Grade uuid",
     *       @OA\Schema(
     *           type="string",
     *           format="uuid",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {   
        $this->filter->setInputData($request->getQueryParams());
        if ($this->filter->isValid()) {
            $this->employeeGradeModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
