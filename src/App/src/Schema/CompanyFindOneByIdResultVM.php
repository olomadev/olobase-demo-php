<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CompanyFindOneByIdResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/CompanyFindOneById",
     * )
     */
    public $data;
}
