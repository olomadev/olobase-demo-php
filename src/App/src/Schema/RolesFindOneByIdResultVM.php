<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class RolesFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/RolesFindOneById",
     * )
     */
    public $data;
}
