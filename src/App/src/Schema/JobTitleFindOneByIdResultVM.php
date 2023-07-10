<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class JobTitleFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/JobTitleFindOneById",
     * )
     */
    public $data;
}
