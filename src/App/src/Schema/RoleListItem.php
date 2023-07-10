<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class RoleListItem
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
