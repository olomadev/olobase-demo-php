<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class Auth
{
    /**
     * @var string
     * @OA\Property()
     */
    public $username;
    /**
     * @var string
     * @OA\Property()
     */
    public $password;
}
