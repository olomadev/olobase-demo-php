<?php

namespace App\Schema\Auth;

/**
 * @OA\Schema()
 */
class AuthRequest
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
