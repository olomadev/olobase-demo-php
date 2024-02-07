<?php

declare(strict_types=1);

namespace App\Handler\JobTitles;

use App\Model\JobTitleModel;
use App\Filter\JobTitles\DeleteFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private JobTitleModel $jobTitleModel,        
        private DeleteFilter $filter,
        private Error $error,
    ) 
    {
        $this->jobTitleModel = $jobTitleModel;
        $this->filter = $filter;
        $this->error = $error;
    }
    
    /**
     * @OA\Delete(
     *   path="/jobtitles/delete/{jobTitleId}",
     *   tags={"Job Titles"},
     *   summary="Delete job title",
     *   operationId="jobTitles_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="jobTitleId",
     *       required=true,
     *       description="JobTitle uuid",
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
            $this->jobTitleModel->delete(
                $this->filter->getValue('id')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }
}
