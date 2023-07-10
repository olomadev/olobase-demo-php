<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PaymentTypeFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/PaymentTypeFindOneById",
     * )
     */
    public $data;
}
