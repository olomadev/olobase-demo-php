<?php

namespace App\Schema\Employess;

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
