<?php

namespace App\Schema\FailedLogins;

/**
 * @OA\Schema()
 */
class FailedLoginsFindAllByPagingObject
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
    public $username;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $attemptedAt;
    /**
     * @var string
     * @OA\Property()
     */
    public $userAgent;
    /**
     * @var string
     * @OA\Property()
     */
    public $ip;
}
