<?php

namespace App\Schema\JobTitleLists;

/**
 * @OA\Schema()
 */
class JobTitleListImport
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
