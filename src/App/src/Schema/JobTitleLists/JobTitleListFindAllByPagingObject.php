<?php

namespace App\Schema\JobTitleLists;

/**
 * @OA\Schema()
 */
class JobTitleListFindAllByPagingObject
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
