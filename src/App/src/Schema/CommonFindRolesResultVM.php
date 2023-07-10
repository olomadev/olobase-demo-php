<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CommonFindRolesResultVM
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
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="level",
    *             type="number",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
