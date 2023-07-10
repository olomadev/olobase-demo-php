<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ResetPassword
{
    /**
    * @var string
    * @OA\Property()
    */
    public $newPassword;
}
