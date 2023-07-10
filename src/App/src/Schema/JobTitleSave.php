<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class JobTitleSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
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
    public $jobTitleListId;
    /**
     * @var string
     * @OA\Property()
     */
    public $jobTitleName;
}
