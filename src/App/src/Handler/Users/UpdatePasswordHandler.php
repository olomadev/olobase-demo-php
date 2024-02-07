<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use App\Filter\Users\PasswordSaveFilter;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdatePasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,        
        private PasswordSaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->userModel = $userModel;
        $this->error = $error;
        $this->filter = $filter;
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->userModel->updatePasswordById(
                $inputFilter->getValue('id'),
                $inputFilter->getValue('password')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
