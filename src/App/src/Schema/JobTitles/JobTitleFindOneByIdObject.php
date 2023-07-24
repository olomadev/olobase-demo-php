<?php

namespace App\Schema\JobTitles;

/**
 * @OA\Schema()
 */
class JobTitleFindOneById
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
    public $jobTitleName;
}
