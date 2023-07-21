<?php

namespace App\Schema\Roles;

/**
 * @OA\Schema()
 */
class RolesFindOneByIdObject
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
    public $roleName;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleKey;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleLevel;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="permId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="moduleName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="method",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="route",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="action",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $rolePermissions;
}
