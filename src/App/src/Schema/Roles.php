<?php

namespace App\Schema;

/**
 * Group tabanlı roller
 *
 * https://stackoverflow.com/questions/50969509/user-role-permissions-and-a-specific-group-rbac
 */
/**
 * @OA\Schema()
 */
class Roles
{
    /**
     * maxLength: 36
     * @var string
     * @OA\Property(
     *     format="uuid",
     * )
     */
    public $roleId;
    /**
     * maxLength: 60
     * @var string
     * @OA\Property()
     */
    public $roleName;
}
