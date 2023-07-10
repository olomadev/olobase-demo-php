<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class WorkplaceListItem
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
    public $companyName;
    /**
     * @var string
     * @OA\Property()
     */
    public $workplaceName;
    /**
     * maxLength: 100
     * @var string
     * @OA\Property()
     */
    public $registrationNumber;
    /**
     * @var string
     * @OA\Property()
     */
    public $address;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
}
