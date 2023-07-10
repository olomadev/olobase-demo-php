<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class JobTitleListItem
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
