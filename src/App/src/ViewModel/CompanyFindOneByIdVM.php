<?php

namespace App\ViewModel;

class CompanyFindOneByIdVM
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
            'companyName' => (string)$row['companyName'],
            'companyShortName' => (string)$row['companyShortName'],
            'taxOffice' => (string)$row['taxOffice'],
            'taxNumber' => (string)$row['taxNumber'],
            'address' => (string)$row['address'],
            'createdAt' => (string)$row['createdAt'],
        ];
		return $data;
	}
}