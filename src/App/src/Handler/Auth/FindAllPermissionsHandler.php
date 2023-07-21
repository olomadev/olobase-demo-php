<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\AuthModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllPermissionsHandler implements RequestHandlerInterface
{
    public function __construct(AuthModel $authModel)
    {
        $this->authModel = $authModel;
    }

    /**
     * @OA\Get(
     *   path="/auth/findAllPermissions",
     *   tags={"Auth"},
     *   summary="Get all permissions before the login",
     *   operationId="auth_findAllPermissions",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->authModel->findAllPermissions();
        return new JsonResponse(
            [
                'data' => $data
            ]
        );
    }

}
