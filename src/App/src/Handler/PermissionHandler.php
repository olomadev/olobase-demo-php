<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use DateTime;
use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\PermissionModel;
use App\ViewModel\PermissionsFindOneByIdVM;
use App\Entity\PermissionsEntity;
use App\Schema\PermissionSave;
use App\Filter\PermissionSaveFilter;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class PermissionHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        PermissionModel $permissionModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->permissionModel = $permissionModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/permissions/findAll",
     *   tags={"Permissions"},
     *   summary="Find all permissions",
     *   operationId="permissions_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionsFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->permissionModel->findAllPermissions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/permissions/findAllByPaging",
     *   tags={"Permissions"},
     *   summary="Find all permissions",
     *   operationId="permissions_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="$filters",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionsFindAllResultVM"),
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
        // q=ersin+güvenç&CostshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->permissionModel->findAllByPaging($get);

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
     *   path="/permissions/findOneById/{permId}",
     *   tags={"Permissions"},
     *   summary="Find one item data",
     *   operationId="permissions_findOneById",
     *
     *   @OA\Parameter(
     *       name="permId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionsFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $permId)
    {
        $row = $this->permissionModel->findOneById($permId);
        if ($row) {
            $viewModel = new PermissionsFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }

    /**
     * @OA\Post(
     *   path="/permissions/create",
     *   tags={"Permissions"},
     *   summary="Create a new permission",
     *   operationId="permissions_create",
     *
     *   @OA\RequestBody(
     *     description="Create a new permission",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionSave"),
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
    public function onPostCreate(array $post, string $permId = null)
    {
        if ($permId) { // copy support
            $post = $this->permissionModel->copy($permId);
        }
        $inputFilter = $this->filter->get(PermissionSaveFilter::class);
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PermissionSave::class,
                [
                    'permissions' => PermissionsEntity::class,
                ]
            );
            $data['permId'] = $inputFilter->getValue('id');
            $this->permissionModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/permissions/update/{permId}",
     *   tags={"Permissions"},
     *   summary="Update permission",
     *   operationId="permissions_update",
     *
     *   @OA\Parameter(
     *       name="permId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update permission",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionSave"),
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
    public function onPutUpdate(array $post, string $permId)
    {   
        $inputFilter = $this->filter->get(permissionsaveFilter::class);
        $post['id'] = $permId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PermissionSave::class,
                [
                    'permissions' => PermissionsEntity::class,
                ]
            );
            $data['permId'] = $inputFilter->getValue('id');
            $this->permissionModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/permissions/delete/{permId}",
     *   tags={"Permissions"},
     *   summary="Delete permission",
     *   operationId="permissions_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="permId",
     *       required=true,
     *       description="Permission uuid",
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
    public function onDeleteDelete(string $permId)
    {
        $this->permissionModel->delete($permId);
        return new JsonResponse([]);
    }

}
