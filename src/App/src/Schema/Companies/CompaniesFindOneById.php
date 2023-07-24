<?php

namespace App\Schema\Companies;

/**
 * @OA\Schema()
 */
class CompaniesFindOneById
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/CompaniesFindOneByIdObject",
     * )
     */
    public $data;
}
