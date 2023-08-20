<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use App\Filter\Auth\ResetPasswordFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;

class ResetPasswordHandler implements RequestHandlerInterface
{
    public function __construct(
        private AuthModel $authModel,
        private ResetPasswordFilter $filter,
        private Error $error
    ) {
        $this->authModel = $authModel;
        $this->filter = $filter;
        $this->error = $error;
    }

    /**
     * @OA\Post(
     *   path="/auth/resetPassword",
     *   tags={"Auth"},
     *   summary="Send reset password code to user",
     *   operationId="auth_resetPassword",
     *
     *   @OA\RequestBody(
     *     description="Send reset password request",
     *     @OA\JsonContent(ref="#/components/schemas/ResetPassword"),
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
            $username = $this->filter->getValue('email');
            $password = $this->authModel->generateResetPassword($username);

            // $userRow = $this->authModel->findOneByUsername($username);
            //
            // Send reset password email to users
            //
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([]);
    }

}
