<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class WorkplaceItem
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
