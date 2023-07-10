<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ConfirmedDepartmentItem
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
