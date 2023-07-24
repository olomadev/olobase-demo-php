<?php

namespace App\Schema\Companies;

/**
 * @OA\Schema()
 */
class CompanySave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $companyId;
    /**
     * @var string
     * @OA\Property()
     */
    public $companyName;
    /**
     * @var string
     * @OA\Property()
     */
    public $companyShortName;
    /**
     * @var string
     * @OA\Property()
     */
    public $taxOffice;
    /**
     * @var string
     * @OA\Property()
     */
    public $taxNumber;
    /**
     * @var string
     * @OA\Property()
     */
    public $address;
    /**
     * @var string
     * @OA\Property()
     */
    public $createdAt;
}
