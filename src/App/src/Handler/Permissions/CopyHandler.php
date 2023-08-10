<?php

declare(strict_types=1);

namespace App\Handler\Permissions;

use App\Model\PermissionModel;
use App\Entity\PermissionsEntity;
use App\Schema\Permissions\PermissionSave;
use App\Filter\Permissions\SaveFilter;
use Oloma\Php\DataManagerInterface;
use Oloma\Php\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CopyHandler implements RequestHandlerInterface
{
    public function __construct(
        private PermissionModel $permissionModel,
        private DataManagerInterface $dataManager,
        private SaveFilter $filter,
        private Error $error,
    ) 
    {
        $this->permissionModel = $permissionModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
        $this->filter = $filter;
    }
    
    /**
     * @OA\Post(
     *   path="/permissions/copy/{permId}",
     *   tags={"Permissions"},
     *   summary="Copy a permission",
     *   operationId="permissions_copy",
     *
     *   @OA\RequestBody(
     *     description="Create a new permission",
     *     @OA\JsonContent(ref="#/components/schemas/PermissionSave"),
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
        $permId = $request->getAttribute("permId");
        $post = $this->permissionModel->copy($permId);
        $this->filter->setInputData($post);
        $data = array();
        $response = array();
        if ($this->filter->isValid()) {
            $this->dataManager->setInputFilter($this->filter);
            $data = $this->dataManager->getEntityData(
                PermissionSave::class,
                [
                    'permissions' => PermissionsEntity::class,
                ]
            );
            $this->permissionModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse($response);     
    }
}
