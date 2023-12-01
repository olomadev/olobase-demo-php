<?php

namespace App\Schema\Auth;

/**
 * @OA\Schema()
 */
class ChangePassword
{
    /**
    * @var string
    * @OA\Property()
    */
    public $newPassword;
    /**
    * @var string
    * @OA\Property()
    */
    public $newPasswordConfirm;
}
