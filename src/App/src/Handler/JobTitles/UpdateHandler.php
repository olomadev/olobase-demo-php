<?php

declare(strict_types=1);

namespace App\Handler\JobTitles;

use App\Model\JobTitleModel;
use App\Entity\JobTitlesEntity;
use App\Schema\JobTitles\JobTitleSave;
use App\Filter\JobTitles\SaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private JobTitleModel $jobTitleModel,
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
     *   path="/jobtitles/update/{jobTitleId}",
     *   tags={"Job Titles"},
     *   summary="Update job title",
     *   operationId="jobTitles_update",
     *
     *   @OA\Parameter(
     *       name="jobTitleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update customer",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleSave"),
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
        $jobTitleId = $request->getAttribute("jobTitleId");
        $post = $request->getParsedBody();
        $post['id'] = $jobTitleId;

        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                JobTitleSave::class,
                [
                    'jobTitles' => JobTitlesEntity::class,
                ]
            );
            $data['jobTitleId'] = $this->filter->getValue('id');
            $this->jobTitleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}