<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/EmployeeFindOneById",
     * )
     */
    public $data;
}
