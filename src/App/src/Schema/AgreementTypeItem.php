<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AgreementTypeItem
{
    /**
     * @var string
     * @OA\Property()
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
}
