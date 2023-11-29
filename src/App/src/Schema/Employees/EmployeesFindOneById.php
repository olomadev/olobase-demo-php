<?php

namespace App\Schema\Employees;

/**
 * @OA\Schema()
 */
class EmployeesFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/EmployeesFindOneByIdObject",
     * )
     */
    public $data;
}
