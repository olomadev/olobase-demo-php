<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeListSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $employeeListId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $yearId;
    /**
     * @var string
     * @OA\Property()
     */
    public $listName;
}
