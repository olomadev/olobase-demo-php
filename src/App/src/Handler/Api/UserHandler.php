<?php

declare(strict_types=1);

namespace App\Handler\Api;

use Exception;
use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\UserModel;
use App\Schema\UserSave;
use App\Entity\UsersEntity;
use App\Entity\UserRolesEntity;
use App\Filter\UserSaveFilter;
use App\Filter\PasswordSaveFilter;
use App\ViewModel\UsersFindOneByIdVM;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class UserHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        UserModel $userModel,
        InputFilterPluginManager $filter,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->userModel = $userModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/users/findAll",
     *   tags={"Users"},
     *   summary="Find all users by autocompleter",
     *   operationId="users_findAll",
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
        $data = $this->userModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/users/findAllByPaging",
     *   tags={"Users"},
     *   summary="Find all users by pagination",
     *   operationId="users_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/UserFindAllByPageResultVM"),
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
        $paginator = $this->userModel->findAllByPaging($get);

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
     *   path="/users/findOneById/{userId}",
     *   tags={"Users"},
     *   summary="Find item data",
     *   operationId="users_findOneById",
     *
     *   @OA\Parameter(
     *       name="userId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/UserFindOneByIdResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOneById(string $userId)
    {
        $row = $this->userModel->findOneById($userId);
        if ($row) {
            $viewModel = new UsersFindOneByIdVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }

    /**
     * @OA\Post(
     *   path="/users/create",
     *   tags={"Users"},
     *   summary="Create a new user",
     *   operationId="users_create",
     *
     *   @OA\RequestBody(
     *     description="Create a new user",
     *     @OA\JsonContent(ref="#/components/schemas/UserSave"),
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
        // replace id keys to roleId
        // 
        foreach($post['userRoles'] as $key => $val) {
            $post['userRoles'][$key]['roleId'] = $val['id'];
            $post['userRoles'][$key]['userId'] = $post['id'];
        }
        $inputFilter = $this->filter->get(UserSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                UserSave::class,
                [
                    'users' => UsersEntity::class,
                    'userRoles' => UserRolesEntity::class,
                ]
            );
            $data['userId'] = $inputFilter->getValue('id');
            $this->userModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/users/update/{userId}",
     *   tags={"Users"},
     *   summary="Update user",
     *   operationId="users_update",
     *
     *   @OA\Parameter(
     *       name="userId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update user",
     *     @OA\JsonContent(ref="#/components/schemas/UserSave"),
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
    public function onPutUpdate(array $post, string $userId)
    {   
        // replace id keys to roleId
        // 
        foreach($post['userRoles'] as $key => $val) {
            $post['userRoles'][$key]['roleId'] = $val['id'];
            $post['userRoles'][$key]['userId'] = $userId;
        }
        $inputFilter = $this->filter->get(UserSaveFilter::class);
        $post['id'] = $userId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                UserSave::class,
                [
                    'users' => UsersEntity::class,
                    'userRoles' => UserRolesEntity::class,
                ]
            );
            $data['userId'] = $inputFilter->getValue('id');
            $this->userModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Put(
     *   path="/users/updatePassword/{userId}",
     *   tags={"Users"},
     *   summary="Update user passwors",
     *   operationId="users_updatePassword",
     *
     *   @OA\Parameter(
     *       name="userId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update user",
     *     @OA\JsonContent(ref="#/components/schemas/PasswordSave"),
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
    public function onPutUpdatePassword(array $post, string $userId)
    {   
        $inputFilter = $this->filter->get(PasswordSaveFilter::class);
        $post['id'] = $userId;
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->userModel->updatePasswordById($userId, $inputFilter->getValue('password'));
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/users/delete/{permId}",
     *   tags={"Users"},
     *   summary="Delete user",
     *   operationId="users_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="userId",
     *       required=true,
     *       description="user uuid",
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
    public function onDeleteDelete(string $userId)
    {
        $this->userModel->delete($userId);
        return new JsonResponse([]);
    }

}
