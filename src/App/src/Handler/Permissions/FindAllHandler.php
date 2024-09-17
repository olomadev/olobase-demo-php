<?php

declare(strict_types=1);

namespace App\Handler\Permissions;

use App\Model\PermissionModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private PermissionModel $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * @OA\Get(
     *   path="/permissions/findAll",
     *   tags={"Permissions"},
     *   summary="Find all permissions",
     *   operationId="permissions_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionsFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->permissionModel->findAllPermissions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
