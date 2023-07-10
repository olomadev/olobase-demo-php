<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\UserModel;
use App\Entity\UsersEntity;
use App\Schema\AccountSave;
use App\Filter\AccountSaveFilter;
use App\Filter\PasswordUpdateFilter;
use App\ViewModel\AccountFindMeVM;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class AccountHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        UserModel $userModel,
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
     *   path="/account/findMe",
     *   tags={"Account"},
     *   summary="Find my account data",
     *   operationId="account_findOneById",
     *  
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AccountFindMeResultVM"),
     *   ),
     *)
     **/
    public function onGetFindMe()
    {
        $user = $this->request->getAttribute(UserInterface::class); // get id from current token
        $userId = $user->getId();
        $row = $this->userModel->findOneById($userId);
        if ($row) {
            $viewModel = new AccountFindMeVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }

    /**
     * @OA\Put(
     *   path="/account/update",
     *   tags={"Account"},
     *   summary="Update account",
     *   operationId="account_update",
     *
     *   @OA\RequestBody(
     *     description="Update Cost",
     *     @OA\JsonContent(ref="#/components/schemas/AccountSave"),
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
    public function onPutUpdate(array $post)
    {
        $user = $this->request->getAttribute(UserInterface::class);
        $inputFilter = $this->filter->get(AccountSaveFilter::class);
        $inputFilter->setUser($user);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                AccountSave::class,
                [
                    'users' => UsersEntity::class,
                ]
            );
            $data['userId'] = $user->getId();
            $this->userModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Put(
     *   path="/account/updatePassword",
     *   tags={"Account"},
     *   summary="Update password",
     *   operationId="account_updatePassword",
     *
     *   @OA\RequestBody(
     *     description="Update Cost",
     *     @OA\JsonContent(ref="#/components/schemas/ChangePassword"),
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
    public function onPutUpdatePassword(array $post)
    {
        $user = $this->request->getAttribute(UserInterface::class);
        $inputFilter = $this->filter->get(PasswordUpdateFilter::class);
        $inputFilter->setUser($user);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $userId = $user->getId();
            $this->userModel->updatePasswordById($userId, $inputFilter->getValue('newPassword'));
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

}
