<?php

declare(strict_types=1);

namespace App\Handler\Employees;

class FindOneByIdViewModel
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
            'companyId' => json_decode($row['companyId'], true),
            'employeeNumber' => (string)$row['employeeNumber'],
            'name' => (string)$row['name'],
            'surname' => (string)$row['surname'],
            'jobTitleId' => json_decode($row['jobTitleId'], true),
            'gradeId' => json_decode($row['gradeId'], true),
            'employmentStartDate' => $row['employmentStartDate'],
            'employmentEndDate' => $row['employmentEndDate'],
            'employeeChildren' => $row['employeeChildren'],
            'files' => (array)$row['files'],
            'createdAt' => (string)$row['createdAt'],
        ];
        return $data;
    }
}