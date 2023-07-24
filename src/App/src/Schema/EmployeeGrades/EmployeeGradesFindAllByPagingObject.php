<?php

namespace App\Schema\EmployeeGrades;

/**
 * @OA\Schema()
 */
class EmployeeGradesFindAllByPagingObject
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
