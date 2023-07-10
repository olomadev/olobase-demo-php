<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AccountSave
{
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
    /**
     * @var string
     * @OA\Property()
     */
    public $firstname;
    /**
     * @var string
     * @OA\Property()
     */
    public $lastname;
    /**
     * @var string
     * @OA\Property()
     */
    public $themeColor;
    /**
     * @var string
     * @OA\Property()
     */
    public $avatarImage;
}
