<?php

declare(strict_types=1);

namespace App\Handler\Departments;

use App\Model\DepartmentModel;
use App\Filter\Departments\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private DepartmentModel $departmentModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->departmentModel = $departmentModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/departments/delete/{gradeId}",
     *   tags={"Departments"},
     *   summary="Delete employee grade",
     *   operationId="departments_delete",
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
            $this->departmentModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
