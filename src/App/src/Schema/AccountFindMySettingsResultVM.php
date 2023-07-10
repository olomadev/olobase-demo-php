<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class AccountFindMySettingsResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/AccountFindMySettings",
     * )
     */
    public $data;
}
