<?php

namespace App\Schema\Users;

/**
 * @OA\Schema()
 */
class UserFindOneByIdObject
{
   /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
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
    public $password;
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
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
    public $themeColor;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $updatedAt;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $lastLogin;
}
