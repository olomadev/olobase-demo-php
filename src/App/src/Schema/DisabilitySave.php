<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class DisabilitySave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $disabilityId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $yearId;
    /**
     * @var string
     * @OA\Property()
     */
    public $degree;
    /**
     * @var string
     * @OA\Property()
     */
    public $description;
    /**
     * @var number
     * @OA\Property()
     */
    public $discountAmount;
}
