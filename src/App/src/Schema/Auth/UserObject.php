<?php

namespace App\Schema\Auth;

/**
 * @OA\Schema()
 */
class UserObject
{
    /**
     * @var string
     * @OA\Property()
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
     * @var array
     * @OA\Property(
     *     type="array",
     *     @OA\Items(
     *         type="string",
     *     )
     * )
     */
    public $roles;
    /**
     * @var string
     * @OA\Property()
     */
    public $email;
}
