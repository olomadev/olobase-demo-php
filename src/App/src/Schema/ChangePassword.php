<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ChangePassword
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
