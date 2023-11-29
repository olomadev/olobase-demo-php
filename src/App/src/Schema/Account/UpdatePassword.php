<?php

namespace App\Schema\Account;

/**
 * @OA\Schema()
 */
class UpdatePassword
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
