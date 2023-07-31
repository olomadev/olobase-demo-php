<?php

declare(strict_types=1);

namespace App\Handler\JobTitleList;

use App\Model\JobTitleListModel;
use App\Entity\JobTitleListEntity;
use App\Schema\JobTitleList\JobTitleListSave;
use App\Filter\JobTitleList\SaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private JobTitleListModel $jobTitleModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->jobTitleModel = $jobTitleModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Put(
     *   path="/jobtitlelists/update/{listId}",
     *   tags={"JobTitle Lists"},
     *   summary="Update jobtitle list",
     *   operationId="jobTitleLists_update",
     *
     *   @OA\Parameter(
     *       name="listId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update jobtitle list",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleListSave"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $jobTitleListId = $request->getAttribute("listId");
        $post = $request->getParsedBody();
        $post['id'] = $jobTitleListId;

        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                JobTitleListSave::class,
                [
                    'jobTitleList' => JobTitleListEntity::class,
                ]
            );
            $data['jobTitleListId'] = $this->filter->getValue('id');
            $this->jobTitleListModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);
    }
}