<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class NotificationListItem
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $notifyId;
    /**
     * @var string
     * @OA\Property()
     */
    public $notifyName;
    /**
     * @var string
     * @OA\Property()
     */
    public $moduleName;
    /**
     * @var string
     * @OA\Property()
     */
    public $dateName;
    /**
     * @var integer
     * @OA\Property()
     */
    public $days;
    /**
     * @var string
     * @OA\Property()
     */
    public $dayType;
    /**
     * @var integer
     * @OA\Property()
     */
    public $sameDay;
    /**
     * @var string
     * @OA\Property()
     */
    public $atTime;
    /**
     * @var string
     * @OA\Property()
     */
    public $notifyType;
    /**
     * @var string
     * @OA\Property()
     */
    public $message;
    /**
     * @var integer
     * @OA\Property()
     */
    public $active;
    /**
     * @var string
     * @OA\Property()
     */
    public $createdAt;
}
