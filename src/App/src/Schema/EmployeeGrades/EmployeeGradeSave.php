<?php

namespace App\Schema\EmployeeGrades;

/**
 * @OA\Schema()
 */
class EmployeeGradeSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $gradeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $gradeName;
}
