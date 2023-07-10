<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AuthChangePassword
{
    /**
     * @var string
     * @OA\Property()
     */
    public $oldPassword;
    /**
     * @var string
     * @OA\Property()
     */
    public $newPassword;
}
