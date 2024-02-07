<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Model\JobTitleListModel;
use App\Filter\JobTitleLists\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private JobTitleListModel $jobTitleListModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->jobTitleListModel = $jobTitleListModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/jobtitlelists/delete/{listId}",
     *   tags={"JobTitle Lists"},
     *   summary="Delete jobtitle list",
     *   operationId="jobTitleLists_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="listId",
     *       required=true,
     *       description="Jobtitle list uuid",
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
            $this->jobTitleListModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
