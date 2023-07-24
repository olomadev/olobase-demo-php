<?php

namespace App\Schema\Permissions;

/**
 * @OA\Schema()
 */
class PermissionsFindAll
{
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="permId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="moduleName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="route",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="method",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="action",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $data;
}
