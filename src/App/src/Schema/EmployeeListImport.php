<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeListImport
{
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
