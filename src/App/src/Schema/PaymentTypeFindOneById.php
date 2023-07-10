<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PaymentTypeFindOneById
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
    public $paymentTypeName;
}
