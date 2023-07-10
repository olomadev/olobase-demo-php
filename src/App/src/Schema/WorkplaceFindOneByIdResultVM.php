<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class WorkplaceFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/WorkplaceFindOneById",
     * )
     */
    public $data;
}
