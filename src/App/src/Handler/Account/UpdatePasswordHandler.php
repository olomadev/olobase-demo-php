<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Model\UserModel;
use App\Filter\Account\PasswordChangeFilter;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdatePasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,        
        private PasswordChangeFilter $filter,
        private Error $error,
    ) 
    {
        $this->userModel = $userModel;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Put(
     *   path="/account/updatePassword",
     *   tags={"Account"},
     *   summary="Update password",
     *   operationId="account_updatePassword",
     *
     *   @OA\RequestBody(
     *     description="Update Password",
     *     @OA\JsonContent(ref="#/components/schemas/UpdatePassword"),
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
            $userId = $user->getId();
            $this->userModel->updatePasswordById(
                $userId, 
                $this->filter->getValue('newPassword')
            );
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response); 
    }
}
