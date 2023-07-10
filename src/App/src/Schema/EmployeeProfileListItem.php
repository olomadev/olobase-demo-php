<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeProfileListItem
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
    public $gradeName;
}
