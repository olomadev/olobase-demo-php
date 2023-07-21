<?php

declare(strict_types=1);

namespace App\Handler\Roles;

use App\Model\RoleModel;
use App\Entity\RolesEntity;
use App\Entity\RolePermissionsEntity;
use App\Schema\Roles\RoleSave;
use App\Filter\RoleSaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateHandler implements RequestHandlerInterface
{
    public function __construct(
        private RoleModel $roleModel,
        private DataManagerInterface $dataManager,
        private RoleSaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->roleModel = $roleModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Put(
     *   path="/roles/update/{roleId}",
     *   tags={"Roles"},
     *   summary="Update role",
     *   operationId="roles_update",
     *
     *   @OA\Parameter(
     *       name="roleId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update role",
     *     @OA\JsonContent(ref="#/components/schemas/RoleSave"),
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
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                RoleSave::class,
                [
                    'roles' => RolesEntity::class,
                    'rolePermissions' => RolePermissionsEntity::class,
                ]
            );
            $data['roleId'] = $this->filter->getValue('id');
            $this->roleModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);   
    }
}
