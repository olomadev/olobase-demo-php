<?php

declare(strict_types=1);

namespace App\Handler\Users;

use App\Model\UserModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @OA\Get(
     *   path="/users/findOneById",
     *   tags={"Users"},
     *   summary="Find one user by id",
     *   operationId="users_findOneById",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/UsersFindOneById"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute("userId");
        $row = $this->userModel->findOneById($userId);
        if ($row) {
            $viewModel = new FindOneByIdViewModel($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }

}
