<?php

namespace App\Schema\Account;

/**
 * @OA\Schema()
 */
class AccountFindMe
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/AccountFindMeObject",
     * )
     */
    public $data;
}
