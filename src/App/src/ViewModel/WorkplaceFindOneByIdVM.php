<?php

namespace App\ViewModel;

class WorkplaceFindOneByIdVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}
    
	public function getData() : array
	{
        $row = $this->row;
        $data = [
            'id'=> (string)$row['id'],
            'companyId'=> (string)$row['companyId'],
            'companyName'=> (string)$row['companyName'],
            'workplaceName'=> (string)$row['workplaceName'],
            'registrationNumber'=> (string)$row['registrationNumber'],
            'address'=> (string)$row['address'],
            'createdAt' => (string)$row['createdAt'],
        ];
		return $data;
	}
}