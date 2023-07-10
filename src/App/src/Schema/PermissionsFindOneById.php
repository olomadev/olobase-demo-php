<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PermissionsFindOneById
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
    public $route;
    /**
     * @var string
     * @OA\Property()
     */
    public $method;
    /**
     * @var string
     * @OA\Property()
     */
    public $action;
}
