<?php

namespace App\Schema\Account;

/**
 * @OA\Schema()
 */
class AccountFindMeObject
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
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    * )
    */
    public $locale;
    /**
     * @var integer
     * @OA\Property()
     */
    public $emailActivation;
    /**
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/AvatarObject",
    * )
    */
    public $avatar;
}
