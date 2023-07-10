<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AuthResultVM
{
    /**
     * @var string
     * @OA\Property()
     */
    public $token;
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/AuthUser",
     * )
     */
    public $user;
    /**
     * @var string
     * @OA\Property(
     *    description="Expiration date of token",
     *    format="date-time"
     * )
     */
    public $expiresAt;
}
