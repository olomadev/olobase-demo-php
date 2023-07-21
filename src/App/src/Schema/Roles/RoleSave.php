<?php

namespace App\Schema\Roles;

/**
 * @OA\Schema()
 */
class RoleSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $roleId;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleKey;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleName;
    /**
     * @var number
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
    *           )
    *     ),
    *  );
    */
    public $rolePermissions;
}
