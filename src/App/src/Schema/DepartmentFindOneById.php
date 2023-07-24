<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class DepartmentFindOneById
{
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $id;
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
     * @OA\Property()
     */
    public $yearId;
    /**
     * @var string
     * @OA\Property()
     */
    public $departmentName;
    /**
     * @var string
     * @OA\Property()
     */
    public $subDepartmentName;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerName;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerSurname;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerPhoneAreaCodeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerPhone;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerMobileAreaCodeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerMobile;
    /**
     * @var string
     * @OA\Property()
     */
    public $managerEmail;
}