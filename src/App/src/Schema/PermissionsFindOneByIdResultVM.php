<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PermissionsFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/PermissionsFindOneById",
     * )
     */
    public $data;
}
