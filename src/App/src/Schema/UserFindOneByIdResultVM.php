<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class UserFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/UserFindOneById",
     * )
     */
    public $data;
}
