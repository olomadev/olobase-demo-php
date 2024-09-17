<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(private RoleModel $roleModel)
    {
        $this->roleModel = $roleModel;
    }

    /**
     * @OA\Get(
     *   path="/roles/findAll",
     *   tags={"Roles"},
     *   summary="Find all roles",
     *   operationId="roles_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAll"),
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
        $data = $this->roleModel->findRoles($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
