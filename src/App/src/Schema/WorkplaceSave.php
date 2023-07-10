<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class WorkplaceSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $workplaceId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $companyId;
    /**
     * maxLength: 255
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
}
