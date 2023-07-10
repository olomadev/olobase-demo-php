<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeListItem
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
     * @OA\Property()
     */
    public $listName;
    /**
     * @var string
     * @OA\Property()
     */
    public $pernetNumber;
    /**
     * @var string
     * @OA\Property()
     */
    public $workplaceName;
    /**
     * @var string
     * @OA\Property()
     */
    public $customerShortName;
    /**
     * @var string
     * @OA\Property()
     */
    public $customerColor;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="id",
    *             type="string",
    *           ), 
    *     ),
    *  );
    */
    public $sows;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
    /**
     * @var string
     * @OA\Property()
     */
    public $middleName;
    /**
     * @var string
     * @OA\Property()
     */
    public $surname;
    /**
     * @var string
     * @OA\Property()
     */
    public $secondSurname;
    /**
     * @var string
     * @OA\Property()
     */
    public $fullname;
    /**
     * @var string
     * @OA\Property()
     */
    public $tckn;
    /**
     * @var string
     * @OA\Property()
     */
    public $countryId;
    /**
     * @var string
     * @OA\Property()
     */
    public $passportNo;
    /**
     * @var string
     * @OA\Property()
     */
    public $genderId;
    /**
     * @var string
     * @OA\Property()
     */
    public $bloodTypeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $phone;
    /**
     * @var string
     * @OA\Property()
     */
    public $mobile1;
    /**
     * @var string
     * @OA\Property()
     */
    public $mobile2;
    /**
     * @var string
     * @OA\Property()
     */
    public $email1;
    /**
     * @var string
     * @OA\Property()
     */
    public $email2;
    /**
     * @var string
     * @OA\Property()
     */
    public $address1;
    /**
     * @var string
     * @OA\Property()
     */
    public $address2;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
}
