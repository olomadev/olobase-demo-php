<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class MinWageListItem
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
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $yearId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $monthId;
    /**
     * @var number
     * @OA\Property()
     */
    public $daily;
    /**
     * @var number
     * @OA\Property()
     */
    public $monthly;    
}
