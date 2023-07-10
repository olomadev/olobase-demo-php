<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class GenderItem
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
