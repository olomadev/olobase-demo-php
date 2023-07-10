<?php

namespace App\ViewModel;

class AccountFindOneByIdVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}

	public function getData() : array
	{
        $row = $this->row;
        $data = [
            'email' => (string)$row['email'],
            'firstname' => (string)$row['firstname'],
            'lastname' => (string)$row['lastname'],
            'active' => (int)$row['active'],
            'themeColor' => (string)$row['themeColor'],
            'emailActivation' => (int)$row['emailActivation'],
        ];
        return $data;
	}
}