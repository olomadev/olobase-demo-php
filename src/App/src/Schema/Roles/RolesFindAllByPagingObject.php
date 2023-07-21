<?php

namespace App\Schema\Roles;

/**
 * @OA\Schema()
 */
class RolesFindAllByPagingObject
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
    public $roleKey;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleName;
    /**
     * @var string
     * @OA\Property()
     */
    public $roleLevel;
}
