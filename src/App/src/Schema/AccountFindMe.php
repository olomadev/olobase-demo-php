<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AccountFindMe
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
     * @var integer
     * @OA\Property()
     */
    public $active;
    /**
     * @var integer
     * @OA\Property()
     */
    public $emailActivation;
}
