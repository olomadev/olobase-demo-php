<?php

namespace App\Schema\Users;

/**
 * @OA\Schema()
 */
class UserSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $userId;
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
    * @var object
    * @OA\Property(
    *     ref="#/components/schemas/AvatarObject",
    * )
    */
    public $avatar;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *     ),
    *  )
    */
    public $userRoles;

}
