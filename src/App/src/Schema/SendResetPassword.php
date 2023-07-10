<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class SendResetPassword
{
    /**
    * @var string
    * @OA\Property()
    */
    public $username;
}
