<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Model\CommonModel;
use App\Utils\DataManager;
use App\ViewModel\JobTitleFindOneByIdVM;
use App\Model\JobTitleModel;
use App\Entity\JobTitlesEntity;
use App\Schema\JobTitleSave;
use App\Filter\JobTitleSaveFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class JobTitlesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        JobTitleModel $jobTitleModel,
        CommonModel $commonModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->jobTitleModel = $jobTitleModel;
        $this->commonModel = $commonModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/jobTitles/findAll",
     *   tags={"Common"},
     *   summary="Find all job titles",
     *   operationId="jobTitles_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->commonModel->findJobTitles();
        return new JsonResponse([
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *   path="/jobtitles/findAllByPaging",
     *   tags={"Job Titles"},
     *   summary="Find all job titles by pagination",
     *   operationId="jobTitles_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_page",
     *       in="query",
     *       required=false,
     *       description="Page number",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_perPage",
     *       in="query",
     *       required=false,
     *       description="Per page",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_sort",
     *       in="query",
     *       required=false,
     *       description="Order items",
     *       @OA\Schema(
     *           type="array",
     *           @OA\Items()
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleFindAllByPageResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAllByPaging(array $get)
    {
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 5 : (int)$get['_perPage'];

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->jobTitleModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => paginatorJsonDecode($paginator->getCurrentItems()),
        ]);
    }

    /**
     * @OA\Get(
     *   path="/jobtitles/findOneById/{jobTitleId}",
     *   tags={"Job Titles"},
     *   summary="Find item data",
     *   operationId="jobTitles_findOneById",
     *
     *   @OA\Parameter(
     *       name="jobTitleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $jobTitleId)
    {
        $row = $this->jobTitleModel->findOneById($jobTitleId);
        if ($row) {
            $viewModel = new JobTitleFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/jobtitles/create",
     *   tags={"Job Titles"},
     *   summary="Create a new job title",
     *   operationId="jobTitles_create",
     *
     *   @OA\RequestBody(
     *     description="Create new job title",
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
    public function onPostCreate(array $post)
    {
        $inputFilter = $this->filter->get(JobTitleSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                JobTitleSave::class,
                [
                    'jobTitles' => JobTitlesEntity::class,
                ]
            );
            $data['jobTitleId'] = $inputFilter->getValue('id');
            $this->jobTitleModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
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
    public function onPutUpdate(array $post, string $jobTitleId)
    {   
        $inputFilter = $this->filter->get(JobTitleSaveFilter::class);
        $post['id'] = $jobTitleId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                JobTitleSave::class,
                [
                    'jobTitles' => JobTitlesEntity::class,
                ]
            );
            $data['jobTitleId'] = $inputFilter->getValue('id');
            $this->jobTitleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
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
    public function onDeleteDelete(string $jobTitleId)
    {
        $this->jobTitleModel->delete($jobTitleId);
        return new JsonResponse([]);
    }
}
