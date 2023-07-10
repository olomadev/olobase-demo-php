<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AccountFindMySettings
{
    /**
     * @var string
     * @OA\Property()
     */
    public $resource;
    /**
     * @var string
     * @OA\Property()
     */
    public $filters;
    /**
     * @var string
     * @OA\Property()
     */
    public $columns;
}
