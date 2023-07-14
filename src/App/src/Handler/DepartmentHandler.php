<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\DataManager;
use App\ViewModel\DepartmentFindOneByIdVM;
use App\Model\DepartmentModel;
use App\Entity\DepartmentsEntity;
use App\Schema\DepartmentSave;
use App\Filter\DepartmentSaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class DepartmentHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        DepartmentModel $departmentModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->departmentModel = $departmentModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/departments/findAll",
     *   tags={"Departments"},
     *   summary="Find all departments",
     *   operationId="departments_findAll",
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
        $data = $this->departmentModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/departments/findAllByPaging",
     *   tags={"Departments"},
     *   summary="Find all departments by pagination",
     *   operationId="departments_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/DepartmentFindAllByPageResultVM"),
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
        // q=ersin+güvenç&customerShortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->departmentModel->findAllByPaging($get);

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
     *   path="/departments/findOneById/{departmentId}",
     *   tags={"Departments"},
     *   summary="Find department data",
     *   operationId="departments_findOneById",
     *
     *   @OA\Parameter(
     *       name="departmentId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/DepartmentFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $departmentId)
    {
        $row = $this->departmentModel->findOneById($departmentId);
        if ($row) {
            $viewModel = new DepartmentFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Post(
     *   path="/departments/create",
     *   tags={"Departments"},
     *   summary="Create a new customer deparment",
     *   operationId="departments_create",
     *
     *   @OA\RequestBody(
     *     description="Create new customer",
     *     @OA\JsonContent(ref="#/components/schemas/DepartmentSave"),
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
        $inputFilter = $this->filter->get(DepartmentSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                DepartmentSave::class,
                [
                    'departments' => DepartmentsEntity::class,
                ]
            );
            $data['departmentId'] = $inputFilter->getValue('id');
            $this->departmentModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/departments/update/{departmentId}",
     *   tags={"Departments"},
     *   summary="Update customer department",
     *   operationId="departments_update",
     *
     *   @OA\Parameter(
     *       name="departmentId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update customer",
     *     @OA\JsonContent(ref="#/components/schemas/DepartmentSave"),
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
    public function onPutUpdate(array $post, string $departmentId)
    {   
        $inputFilter = $this->filter->get(DepartmentSaveFilter::class);
        $post['id'] = $departmentId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                DepartmentSave::class,
                [
                    'departments' => DepartmentsEntity::class,
                ]
            );
            $data['departmentId'] = $inputFilter->getValue('id');
            $this->departmentModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/departments/delete/{departmentId}",
     *   tags={"Departments"},
     *   summary="Delete department",
     *   operationId="departments_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="departmentId",
     *       required=true,
     *       description="Department uuid",
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
    public function onDeleteDelete(string $departmentId)
    {
        $this->departmentModel->delete($departmentId);
        return new JsonResponse([]);
    }
}
