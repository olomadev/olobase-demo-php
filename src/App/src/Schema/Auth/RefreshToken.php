<?php

namespace App\Schema\Auth;

/**
 * @OA\Schema()
 */
class RefreshToken
{
    /**
    * @var string
    * @OA\Property()
    */
    public $token;
}
