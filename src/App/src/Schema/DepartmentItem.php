<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class DepartmentItem
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
