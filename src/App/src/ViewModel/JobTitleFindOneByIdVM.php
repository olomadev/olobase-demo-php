<?php

namespace App\ViewModel;

class JobTitleFindOneByIdVM
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
            'jobTitleName' => (string)$row['jobTitleName'],
        ];
		return $data;
	}
}