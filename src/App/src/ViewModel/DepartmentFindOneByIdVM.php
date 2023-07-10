<?php

namespace App\ViewModel;

use function jsonEncode;

class DepartmentFindOneByIdVM
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
            'customerId' => (string)$row['customerId'],
            'customerName' => (string)$row['customerShortName'],
            'departmentName' => (string)$row['departmentName'],
            'subDepartmentName' => (string)$row['subDepartmentName'],
            'managerName' => (string)$row['managerName'],
            'managerSurname' => (string)$row['managerSurname'],
            'managerPhoneAreaCodeId' => (string)$row['managerPhoneAreaCodeId'],
            'managerPhone' => (string)$row['managerPhone'],
            'managerMobileAreaCodeId' => (string)$row['managerMobileAreaCodeId'],
            'managerMobile' => (string)$row['managerMobile'],
            'managerEmail' => (string)$row['managerEmail'],
        ];
        $data['subDepartments'] = empty($row['subDepartments']) ? jsonEncode([]) : jsonEncode($row['subDepartments']);
		return $data;
	}
}