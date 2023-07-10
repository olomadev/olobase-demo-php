<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class SalaryListItem
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
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $employeeId;
    /**
    * @var string
    * @OA\Property()
    */
    public $paymentTypeParamId;
    /**
     * @var number
     * @OA\Property()
     */
    public $amount;
    /**
     * @var number
     * @OA\Property()
     */
    public $day;   
}
