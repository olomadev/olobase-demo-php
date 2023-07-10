<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class DepartmentFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/DepartmentFindOneById",
     * )
     */
    public $data;
}
