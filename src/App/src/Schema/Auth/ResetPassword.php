<?php

namespace App\Schema\Auth;

/**
 * @OA\Schema()
 */
class ResetPassword
{
    /**
    * @var string
    * @OA\Property()
    */
    public $email;
}
