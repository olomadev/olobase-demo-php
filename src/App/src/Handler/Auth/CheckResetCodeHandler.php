<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Model\UserModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckResetCodeHandler implements RequestHandlerInterface
{
    public function __construct(UserModel $userModel) 
    {
        $this->userModel = $userModel;
    }

    /**
     * @OA\Get(
     *   path="/auth/checkResetCode",
     *   tags={"Auth"},
     *   summary="Check reset code is valid or expired",
     *   operationId="auth_checkResetCode",
     *   
     *   @OA\Parameter(
     *       in="query",
     *       name="resetCode",
     *       required=true,
     *       description="File id",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        if (empty($get['resetCode'])) {
            return new JsonResponse(
                [
                    'data' => [
                        'error' => "Empty reset code",
                    ]
                ], 
                401
            );
        }
        if ($this->userModel->checkResetCode($get['resetCode'])) {
            return new JsonResponse([]);
        }
        return new JsonResponse(
            [
                'data' => [
                    'error' => "Invalid reset code",
                ]
            ], 
            401
        );
    }

}
