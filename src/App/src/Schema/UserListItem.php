<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class UserListItem
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
}
