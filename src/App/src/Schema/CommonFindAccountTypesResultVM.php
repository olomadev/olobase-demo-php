<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CommonFindAccountTypesResultVM
{
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="account_type_id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="account_type_name",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
