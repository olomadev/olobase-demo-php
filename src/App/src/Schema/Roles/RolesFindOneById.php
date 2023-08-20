<?php

namespace App\Schema\Roles;

/**
 * @OA\Schema()
 */
class RolesFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/RolesFindOneByIdObject",
     * )
     */
    public $data;
}
