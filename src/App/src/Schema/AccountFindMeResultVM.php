<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AccountFindMeResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/AccountFindMe",
     * )
     */
    public $data;
}
