<?php

namespace App\Schema\Account;

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
     * @var integer
     * @OA\Property()
     */
    public $active;
    /**
     * @var integer
     * @OA\Property()
     */
    public $emailActivation;
    /**
    * @var string
    * @OA\Property()
    */
    public $avatarImage;
}
