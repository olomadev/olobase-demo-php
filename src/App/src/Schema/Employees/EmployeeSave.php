<?php

namespace App\Schema\Employees;

/**
 * @OA\Schema()
 */
class EmployeeSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $employeeId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $companyId;
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
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $jobTitleId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $gradeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $employmentStartDate;
    /**
     * @var string
     * @OA\Property()
     */
    public $employmentEndDate;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="childId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="childName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="childBirthdate",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeChildren;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="size",
    *             type="number",
    *           ),
    *           @OA\Property(
    *             property="name",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="data",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="type",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $files;

}
