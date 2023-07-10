<?php

namespace App\ViewModel;

class UsersFindOneByIdVM
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
            'firstname' => (string)$row['firstname'],
            'lastname' => (string)$row['lastname'],
            'email' => (string)$row['email'],
            'active' => (int)$row['active'],
            'emailActivation' => (int)$row['emailActivation'],
            'lastLogin' => (string)$row['lastLogin'],
            'createdAt' => (string)$row['createdAt'],
            'password' => null, // default must be null
            'themeColor' => (string)$row['themeColor'],
            'avatarImage' => null,
        ];
        if (! empty($row['avatarImage'])) {
            $data['avatarImage'] = 'data:image/png;base64,'.base64_encode($row['avatarImage']);
        }
        $data['userRoles'] = empty($row['userRoles']) ? null : $row['userRoles'];
		return $data;
	}
}