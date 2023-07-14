<?php

declare(strict_types=1);

namespace App\Handler\Api;

use Exception;
use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\RoleModel;
use App\Schema\RoleSave;
use App\Entity\RolesEntity;
use App\Entity\RolePermissionsEntity;
use App\Filter\RoleSaveFilter;
use App\ViewModel\RolesFindOneByIdVM;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class RoleHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        RoleModel $roleModel,
        InputFilterPluginManager $filter,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->roleModel = $roleModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/roles/findAllByPaging",
     *   tags={"Roles"},
     *   summary="Find all roles by pagination",
     *   operationId="roles_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/RolesFindAllByPageResultVM"),
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
        $paginator = $this->roleModel->findAllByPaging($get);

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
     *   path="/roles/findOneById/{roleId}",
     *   tags={"Roles"},
     *   summary="Find item data",
     *   operationId="roles_findOneById",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/RolesFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $roleId)
    {
        $row = $this->roleModel->findOneById($roleId);
        if ($row) {
            $viewModel = new RolesFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }

    /**
     * @OA\Post(
     *   path="/roles/create",
     *   tags={"Roles"},
     *   summary="Create a new role",
     *   operationId="roles_create",
     *
     *   @OA\RequestBody(
     *     description="Create a new role",
     *     @OA\JsonContent(ref="#/components/schemas/RoleSave"),
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
        $inputFilter = $this->filter->get(RoleSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                RoleSave::class,
                [
                    'roles' => RolesEntity::class,
                    'rolePermissions' => RolePermissionsEntity::class,
                ]
            );
            $data['roleId'] = $inputFilter->getValue('id');
            $this->roleModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/roles/update/{roleId}",
     *   tags={"Roles"},
     *   summary="Update role",
     *   operationId="roles_update",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update role",
     *     @OA\JsonContent(ref="#/components/schemas/RoleSave"),
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
    public function onPutUpdate(array $post, string $roleId)
    {   
        $inputFilter = $this->filter->get(RoleSaveFilter::class);
        $post['id'] = $roleId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                RoleSave::class,
                [
                    'roles' => RolesEntity::class,
                    'rolePermissions' => RolePermissionsEntity::class,
                ]
            );
            $data['roleId'] = $inputFilter->getValue('id');
            $this->roleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/roles/delete/{permId}",
     *   tags={"Roles"},
     *   summary="Delete role",
     *   operationId="roles_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="roleId",
     *       required=true,
     *       description="Role uuid",
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
    public function onDeleteDelete(string $roleId)
    {
        $this->roleModel->delete($roleId);
        return new JsonResponse([]);
    }

}
