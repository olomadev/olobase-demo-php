<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeFindAllBySearchResultVM
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
    *             property="tckn",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="pernetNumber",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
