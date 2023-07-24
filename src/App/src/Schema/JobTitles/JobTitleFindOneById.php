<?php

namespace App\Schema\JobTitles;

/**
 * @OA\Schema()
 */
class JobTitleFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/JobTitleFindOneByIdObject",
     * )
     */
    public $data;
}
