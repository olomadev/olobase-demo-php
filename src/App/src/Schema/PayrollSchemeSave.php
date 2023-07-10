<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class PayrollSchemeSave
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $payrollSchemeId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $companyId;
    /**
    * @var string
    * @OA\Property(
    *     ref="#/components/schemas/ObjectId",
    *     format="uuid",
    * )
    */
    public $workplaceId;
    /**
     * @var string
     * @OA\Property()
     */
    public $schemeName;
    /**
     * @var string
     * @OA\Property()
     */
    public $schemeDescription;
    /**
     * @var string
     * @OA\Property()
     */
    public $startDate;
    /**
     * @var string
     * @OA\Property()
     */
    public $endDate;
}
