<?php

namespace App\Schema\Employees;

/**
 * @OA\Schema()
 */
class EmployeesFindAllBySearch
{
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="employeeId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="customerShortName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="employeeNumber",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
