<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\ViewModel\WorkplaceFindOneByIdVM;
use App\Model\WorkplaceModel;
use App\Entity\WorkplacesEntity;
use App\Schema\WorkplaceSave;
use App\Filter\WorkplaceSaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class WorkplaceHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        WorkplaceModel $workplaceModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->workplaceModel = $workplaceModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/workplaces/findAll",
     *   tags={"Workplaces"},
     *   summary="Find all workplaces",
     *   operationId="workplaces_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/WorkplacesFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->workplaceModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/workplaces/findAllByPaging",
     *   tags={"Workplaces"},
     *   summary="Find all workplaces by pagination",
     *   operationId="workplaces_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/WorkplaceFindAllByPageResultVM"),
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

        // queries:
        // q=ersin+güvenç&WorkplaceshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->workplaceModel->findAllByPaging($get);

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
     *   path="/workplaces/findOneById/{workplaceId}",
     *   tags={"Workplaces"},
     *   summary="Find workplace data",
     *   operationId="workplaces_findOneById",
     *
     *   @OA\Parameter(
     *       name="workplaceId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/WorkplaceFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $workplaceId)
    {
        $row = $this->workplaceModel->findOneById($workplaceId);
        if ($row) {
            $viewModel = new WorkplaceFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/workplaces/create",
     *   tags={"Workplaces"},
     *   summary="Create a new workplace",
     *   operationId="workplaces_create",
     *
     *   @OA\RequestBody(
     *     description="Create new workplace",
     *     @OA\JsonContent(ref="#/components/schemas/WorkplaceSave"),
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
        $inputFilter = $this->filter->get(WorkplaceSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                WorkplaceSave::class,
                [
                    'workplaces' => WorkplacesEntity::class,
                ]
            );
            $data['workplaceId'] = $inputFilter->getValue('id');
            // $user = $this->request->getAttribute(UserInterface::class);
            $this->workplaceModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/workplaces/update/{workplaceId}",
     *   tags={"Workplaces"},
     *   summary="Update Workplace",
     *   operationId="workplaces_update",
     *
     *   @OA\Parameter(
     *       name="workplaceId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update Workplace",
     *     @OA\JsonContent(ref="#/components/schemas/WorkplaceSave"),
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
    public function onPutUpdate(array $post, string $workplaceId)
    {   
        $inputFilter = $this->filter->get(WorkplaceSaveFilter::class);
        $post['id'] = $workplaceId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                WorkplaceSave::class,
                [
                    'workplaces' => WorkplacesEntity::class,
                ]
            );
            $data['workplaceId'] = $inputFilter->getValue('id');
            $this->workplaceModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/workplaces/delete/{workplaceId}",
     *   tags={"Workplaces"},
     *   summary="Delete workplace",
     *   operationId="workplaces_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="workplaceId",
     *       required=true,
     *       description="Workplace uuid",
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
    public function onDeleteDelete(string $workplaceId)
    {
        $this->workplaceModel->delete($workplaceId);
        return new JsonResponse([]);
    }
}
