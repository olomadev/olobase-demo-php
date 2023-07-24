<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Model\UserModel;
use App\Entity\UsersEntity;
use App\Schema\AccountSave;
use App\Filter\AccountSaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,        
        private DataManagerInterface $dataManager,
        private AccountSaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->userModel = $userModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);
        $this->filter->setUser($user);
        $this->filter->setInputData($request->getParsedBody());

        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                AccountSave::class,
                [
                    'users' => UsersEntity::class,
                ]
            );
            $data['userId'] = $user->getId();
            $this->userModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}