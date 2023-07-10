<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeFindOneById
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
    public $pernetNumber;
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $workplaceId;
    /**
     * @var string
     * @OA\Property(
     *     format="uuid"
     * )
     */
    public $customerId;
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
    public $drivingLicenseId;
    /**
     * @var string
     * @OA\Property()
     */
    public $bloodTypeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $size;
    /**
     * @var string
     * @OA\Property()
     */
    public $weight;
    /**
     * @var string
     * @OA\Property()
     */
    public $shoeSizeId;
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
    public $birthdate;
    /**
     * @var string
     * @OA\Property()
     */
    public $employmentStartDate;
    /**
     * @var string
     * @OA\Property()
     */
    public $employmentEndDate;
    /**
     * @var string
     * @OA\Property()
     */
    public $releaseCodeId;
    /**
     * @var string
     * @OA\Property()
     */
    public $seniorityPrincipleDate;
    /**
     * @var string
     * @OA\Property()
     */
    public $employeeTypeId;
    /**
     * @var integer
     * @OA\Property()
     */
    public $disability;
    /**
     * @var string
     * @OA\Property()
     */
    public $disabilityDegree;
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
     * @OA\Property()
     */
    public $bankId;
    /**
     * @var string
     * @OA\Property()
     */
    public $bankBranchCode;
    /**
     * @var string
     * @OA\Property()
     */
    public $bankAccountNo;
    /**
     * @var string
     * @OA\Property()
     */
    public $bankIbanNo;
    /**
     * @var string
     * @OA\Property()
     */
    public $employeeInfoSheetDescription;
    /**
     * @var string
     * @OA\Property(
     *     format="date-time",
     * )
     */
    public $createdAt;
    /**
    *  @var object
    *  @OA\Property(
    *      @OA\Property(
    *          property="schoolTypeId",
    *          type="string",
    *          format="uuid"
    *      ),
    *      @OA\Property(
    *          property="schoolName",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="departmentName",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="educationStartYearId",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="educationEndYearId",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="facultyName",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="graduate",
    *          type="boolean",
    *      ),
    *      @OA\Property(
    *          property="educationalBackgroundId",
    *          type="string",
    *          format="uuid"
    *      ),
    *  );
    */
    public $employeeEducation;
    /**
    *  @var object
    *  @OA\Property(
    *      @OA\Property(
    *          property="militaryStatusId",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="militaryStartDate",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="militaryEndDate",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="marialStatusId",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="spouseNameSurname",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="spouseTckn",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="spouseHasJob",
    *          type="boolean",
    *      ),
    *      @OA\Property(
    *          property="emergencyPersonName",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="emergencyPersonSurname",
    *          type="string",
    *      ),
    *      @OA\Property(
    *          property="emergencyPersonDegreeId",
    *          type="string",
    *      ),
    *  );
    */
    public $employeePersonal;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="childId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="childNameSurname",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="childTckn",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="childBirthdate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="childSchoolName",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeChildren;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="fieldId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="fieldLabel",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="fieldValue",
    *             type="boolean",
    *           ),
    *     ),
    *  );
    */
    public $employeeInfoSheet;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="agreementId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="customerId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="customerName",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="sowId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="customerEmployeeNo",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="departmentId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="departmentName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="subDepartmentId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="subDepartmentName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="agreementTypeId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="agreementTypeName",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="jobTitleId",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="startDate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="endDate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="isEnd",
    *             type="boolean",
    *           ), 
    *           @OA\Property(
    *             property="endReason",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="earlyEndDate",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="currencyId",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="grossSalary",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="netSalary",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="description",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="bonus",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="oss",
    *             type="boolean",
    *           ), 
    *           @OA\Property(
    *             property="car",
    *             type="boolean",
    *           ), 
    *           @OA\Property(
    *             property="ticket",
    *             type="boolean",
    *           ), 
    *           @OA\Property(
    *             property="transportation",
    *             type="boolean",
    *           ), 
    *     ),
    *  );
    */
    public $employeeAgreements;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="jobTitleId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="educationId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="educationStartDate",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="educatioEndDate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="educationValidityEnd",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeEducations;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="jobTitleId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="healthDocId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="healthDocDate",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="healthDocDoctorDate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="healthDocValidityEnd",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeHealthDocs;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="debitId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="employeeId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="debitStartDate",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="debitEndDate",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="debitItemId",
    *             type="uuid",
    *           ),
    *           @OA\Property(
    *             property="debitSerialNo",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="debitDescription",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeDebits;
    /**
    *  @var array
    *  @OA\Property(
    *      type="array",
    *      @OA\Items(
    *           @OA\Property(
    *             property="noteId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="employeeId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="subjectId",
    *             type="string",
    *             format="uuid"
    *           ),
    *           @OA\Property(
    *             property="noteBody",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="noteStatus",
    *             type="string",
    *           ), 
    *           @OA\Property(
    *             property="createdAt",
    *             type="string",
    *           ),
    *           @OA\Property(
    *             property="createdBy",
    *             type="uuid",
    *           ),
    *           @OA\Property(
    *             property="closedBy",
    *             type="uuid",
    *           ),
    *           @OA\Property(
    *             property="closedAt",
    *             type="string",
    *           ),
    *     ),
    *  );
    */
    public $employeeNotes;
    
}
