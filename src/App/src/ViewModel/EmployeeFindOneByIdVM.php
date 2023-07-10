<?php

namespace App\ViewModel;

use function formatDate;
use function formatSize;
use function formatWeight;
use function formatMoney;
use function formatPhone;
use function jsonEncode;

class EmployeeFindOneByIdVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}

	public function getData() : array
	{
        $row = $this->row;
        $data = [
            'id' => (string)$row['id'],
            'pernetNumber' => (string)$row['pernetNumber'],
            'workplaceId' => (string)$row['workplaceId'],
            'workplaceName' => (string)$row['workplaceName'],
            'customerId' => (string)$row['customerId'],
            'customerName' => (string)$row['customerName'],
            'name' => (string)$row['name'],
            'middleName' => (string)$row['middleName'],
            'surname'=> (string)$row['surname'],
            'secondSurname'=> (string)$row['secondSurname'],
            'tckn' => (string)$row['tckn'],
            'countryId' => (string)$row['countryId'], 
            'countryName' => (string)$row['countryName'],
            'passportNo'=> (string)$row['passportNo'],
            'genderId' => (string)$row['genderId'],
            'drivingLicenseId' => (string)$row['drivingLicenseId'],
            'genderName' => (string)$row['genderName'],
            'bloodTypeId' => (string)$row['bloodTypeId'],
            'bloodTypeName' => (string)$row['bloodTypeName'],
            'size' => formatSize($row['size']),
            'weight' => formatWeight($row['weight']),
            'shoeSizeId' => (string)$row['shoeSizeId'],
            'phone' => formatPhone($row['phone']),
            'mobile1' => formatPhone($row['mobile1']),
            'mobile2' => formatPhone($row['mobile2']),
            'birthdate' => formatDate($row['birthdate']),
            'employmentStartDate' => formatDate($row['employmentStartDate']),
            'employmentEndDate' => formatDate($row['employmentEndDate']),
            'seniorityPrincipleDate' => formatDate($row['seniorityPrincipleDate']),
            'employeeTypeId' => (string)$row['employeeTypeId'],
            'disability' => (int)$row['disability'],
            'disabilityDegree' => empty($row['disabilityDegree']) ? null : (string)$row['disabilityDegree'],
            'releaseCodeId' => (string)$row['releaseCodeId'],
            'email1' => (string)$row['email1'],
            'email2' => (string)$row['email2'],
            'address1' => (string)$row['address1'],
            'address2' => (string)$row['address2'],
            'bankId' => (string)$row['bankId'],
            'bankBranchCode' => (string)$row['bankBranchCode'],
            'bankAccountNo' => (string)$row['bankAccountNo'],
            'bankIbanNo' => (string)$row['bankIbanNo'],
            'createdAt' => (string)$row['createdAt'],
            'employeeEducation' => [
                'schoolTypeId' => (string)$row['schoolTypeId'],
                'schoolName' => (string)$row['schoolName'],
                'departmentName' => (string)$row['departmentName'],
                'educationStartYearId' => (string)$row['educationStartYearId'],
                'educationEndYearId' => (string)$row['educationEndYearId'],
                'facultyName' => (string)$row['facultyName'],
                'graduate' => (int)$row['graduate'],
                'educationalBackgroundId' => (string)$row['educationalBackgroundId'],
            ],
            'infoSheet' => [
                'employeeInfoSheetDescription' => (string)$row['employeeInfoSheetDescription'],
            ],
            'employeePersonal' => [
                'militaryStatusId' => (string)$row['militaryStatusId'],
                'militaryStartDate' => (string)$row['militaryStartDate'],
                'militaryEndDate' => (string)$row['militaryEndDate'],
                'marialStatusId' => (string)$row['marialStatusId'],
                'spouseNameSurname' => (string)$row['spouseNameSurname'],
                'spouseTckn' => (string)$row['spouseTckn'],
                'spouseHasJob' => (int)$row['spouseTckn'],
                'emergencyPersonName' => (string)$row['emergencyPersonName'],
                'emergencyPersonSurname' => (string)$row['emergencyPersonSurname'],
                'emergencyPersonDegreeId' => (string)$row['emergencyPersonDegreeId'],
            ],
        ];
        foreach ($row['employeeChildren'] as $key => $child) {
            $row['employeeChildren'][$key]['childBirthdate'] = formatDate($child['childBirthdate']);
        }
        $data['sows'] = (array)$row['sows'];
        $data['employeeChildren'] = jsonEncode($row['employeeChildren']);
        $data['employeeInfoSheet'] = jsonEncode($row['employeeInfoSheet']);
        
        foreach ($row['employeeAgreements'] as $agKey => $ag) {
            $currencyId = $row['employeeAgreements'][$agKey]['currencyId'];
            $row['employeeAgreements'][$agKey]['startDate'] = formatDate($ag['startDate']);
            $row['employeeAgreements'][$agKey]['endDate'] = formatDate($ag['endDate']);
            $row['employeeAgreements'][$agKey]['earlyEndDate'] = formatDate($ag['earlyEndDate']);
            $row['employeeAgreements'][$agKey]['isEnd'] = (int)$ag['isEnd'];
            $row['employeeAgreements'][$agKey]['grossSalary'] = formatMoney($ag['grossSalary']);
            $row['employeeAgreements'][$agKey]['netSalary'] = formatMoney($ag['netSalary']);
            $row['employeeAgreements'][$agKey]['bonus'] = formatMoney($ag['bonus']);
            $row['employeeAgreements'][$agKey]['car'] = (int)$ag['car'];
            $row['employeeAgreements'][$agKey]['ticket'] = (int)$ag['ticket'];
            $row['employeeAgreements'][$agKey]['oss'] = (int)$ag['oss'];
            $row['employeeAgreements'][$agKey]['transportation'] = (int)$ag['transportation'];
        }
        $data['employeeAgreements'] = jsonEncode($row['employeeAgreements']);

        foreach ($row['employeeEducations'] as $eduKey => $edug) {
            $row['employeeEducations'][$eduKey]['educationStartDate'] = formatDate($edug['educationStartDate']);
            $row['employeeEducations'][$eduKey]['educationEndDate'] = formatDate($edug['educationEndDate']);
            $row['employeeEducations'][$eduKey]['educationValidity'] = (int)$edug['educationValidity'];
            $row['employeeEducations'][$eduKey]['educationValidityEnd'] = formatDate($edug['educationValidityEnd']);
        }
        $data['employeeEducations'] = jsonEncode($row['employeeEducations']);

        foreach ($row['employeeHealthDocs'] as $heKey => $hdoc) {
            $row['employeeHealthDocs'][$heKey]['healthDocDate'] = formatDate($hdoc['healthDocDate']);
            $row['employeeHealthDocs'][$heKey]['healthDocDoctorDate'] = formatDate($hdoc['healthDocDoctorDate']);
            $row['employeeHealthDocs'][$heKey]['healthDocValidity'] = (int)$hdoc['healthDocValidity'];
            $row['employeeHealthDocs'][$heKey]['healthDocValidityEnd'] = formatDate($hdoc['healthDocValidityEnd']);
        }
        $data['employeeHealthDocs'] = jsonEncode($row['employeeHealthDocs']);

        foreach ($row['employeeDebits'] as $debKey => $dval) {
            $row['employeeDebits'][$debKey]['debitStartDate'] = formatDate($dval['debitStartDate']);
            $row['employeeDebits'][$debKey]['debitEndDate'] = formatDate($dval['debitEndDate']);
        }
        $data['employeeDebits'] = jsonEncode($row['employeeDebits']);

        foreach ($row['employeeNotes'] as $noteKey => $nVal) {
            $row['employeeNotes'][$noteKey]['createdAt'] = formatDate($nVal['createdAt']);
            $row['employeeNotes'][$noteKey]['closedAt'] = formatDate($nVal['closedAt']);
        }
        $data['employeeNotes'] = jsonEncode($row['employeeNotes']);
        // $data['costEmployees'] = jsonEncode($row['costEmployees']);
		return $data;
	}
}