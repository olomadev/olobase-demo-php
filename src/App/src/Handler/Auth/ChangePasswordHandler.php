<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\UserModel;
use App\Model\FailedLoginModel;
use App\Filter\Auth\ChangePasswordFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;

class ChangePasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private UserModel $userModel,
        private FailedLoginModel $failedLoginModel,
        private ChangePasswordFilter $filter,
        private Error $error
    ) 
    {
        $this->userModel = $userModel;
        $this->failedLoginModel = $failedLoginModel;
        $this->filter = $filter;
        $this->error = $error;
    }

    /**
     * @OA\Post(
     *   path="/auth/changePassword",
     *   tags={"Auth"},
     *   summary="Changing password after resetting code",
     *   operationId="auth_changePassword",
     *
     *   @OA\RequestBody(
     *     description="Send reset code request",
     *     @OA\JsonContent(ref="#/components/schemas/ChangePassword"),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->filter->setInputData($request->getParsedBody());
        if ($this->filter->isValid()) {

            $resetCode = $this->filter->getValue('resetCode');
            $newPassword = $this->filter->getValue('newPassword');
            $username = $this->userModel->updatePasswordByResetCode($resetCode, $newPassword);
            //
            // delete failed logins attempts 
            // otherwise blocked users can't login
            //
            $this->failedLoginModel->deleteAttemptsByUsername($username);

            return new JsonResponse([]);
        }
        return new JsonResponse($this->error->getMessages($this->filter), 400);
    }

}
