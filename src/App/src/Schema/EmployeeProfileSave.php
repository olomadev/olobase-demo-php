<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeProfileSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $profileId;
    /**
     * @var string
     * @OA\Property()
     */
    public $profileName;
}
