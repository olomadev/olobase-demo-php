<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindOneByIdHandler implements RequestHandlerInterface
{
    public function __construct(RoleModel $roleModel)
    {
        $this->roleModel = $roleModel;
    }

    /**
     * @OA\Get(
     *   path="/roles/findOneById/{roleId}",
     *   tags={"Roles"},
     *   summary="Find item data",
     *   operationId="roles_findOneById",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/RolesFindOneById"),
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $roleId = $request->getAttribute("roleId");
        $row = $this->roleModel->findOneById($roleId);
        if ($row) {
            $viewModel = new FindOneByIdViewModel($row);
            return new JsonResponse(['data' => $viewModel->getData()]);
        }
        return new JsonResponse([], 404);
    }

}
