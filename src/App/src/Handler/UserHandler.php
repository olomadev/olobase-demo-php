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
