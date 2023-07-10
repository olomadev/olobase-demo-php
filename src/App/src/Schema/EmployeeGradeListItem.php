<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeGradeListItem
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
