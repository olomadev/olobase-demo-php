<?php

namespace App\Schema\Employees;

/**
 * @OA\Schema()
 */
class EmployeesFindAllByPagingObject
{
   /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/ObjectId",
     * )
     */
    public $companyId;
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/ObjectId",
     * )
     */
    public $departmentId;
    /**
     * @var string
     * @OA\Property()
     */
    public $employeeNumber;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
    /**
     * @var string
     * @OA\Property()
     */
    public $surname;
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/ObjectId",
     * )
     */
    public $jobTitleId;
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/ObjectId",
     * )
     */
    public $gradeId;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
}
