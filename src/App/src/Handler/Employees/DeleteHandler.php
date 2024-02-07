<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use App\Filter\Employees\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private EmployeeModel $employeeModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->employeeModel = $employeeModel;
        $this->filter = $filter;
        $this->error = $error;
    }

    /**
     * @OA\Delete(
     *   path="/employees/delete/{employeeId}",
     *   tags={"Employees"},
     *   summary="Delete employee",
     *   operationId="employees_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="employeeId",
     *       required=true,
     *       description="Employee uuid",
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
            $this->employeeModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
