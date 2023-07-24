<?php

namespace App\Schema\Common;

/**
 * @OA\Schema()
 */
class AreaCodesFindAll
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
    *             property="phoneMask",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="mobileMask",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
