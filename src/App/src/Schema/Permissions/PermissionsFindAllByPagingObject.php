<?php

namespace App\Schema\Permissions;

/**
 * @OA\Schema()
 */
class PermissionsFindAllByPagingObject
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $moduleName;
    /**
     * @var string
     * @OA\Property()
     */
    public $resource;
    /**
     * @var string
     * @OA\Property()
     */
    public $action;
    /**
     * @var string
     * @OA\Property()
     */
    public $route;
    /**
     * @var string
     * @OA\Property()
     */
    public $method;
}
