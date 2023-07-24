<?php

namespace App\Schema\JobTitles;

/**
 * @OA\Schema()
 */
class JobTitleFindAllByPagingObject
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
    public $listName;
    /**
     * @var string
     * @OA\Property()
     */
    public $jobTitleName;
}
