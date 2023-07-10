<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PaymentTypeSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $paymentTypeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $paymentTypeName;
}
