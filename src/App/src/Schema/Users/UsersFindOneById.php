<?php

namespace App\Schema\Users;

/**
 * @OA\Schema()
 */
class UsersFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/UsersFindOneByIdObject",
     * )
     */
    public $data;
}
