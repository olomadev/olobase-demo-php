<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Entity\UsersEntity;
use App\Schema\Users\UserSave;
use App\Filter\UserSaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,        
        private DataManagerInterface $dataManager,
        private UserSaveFilter $filter,
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $post = $request->getParsedBody();
        $post['id'] = $request->getAttribute('userId');
        $this->filter->setInputData($post);

        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                UserSave::class,
                [
                    'users' => UsersEntity::class,
                ]
            );
            $data['userId'] = $this->filter->getValue('id');
            $this->userModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
