<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class UserFindAllResultVM
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
    *     ),
    *  )
    */
    public $data;
}
