<?php

namespace App\ViewModel;

class AuthResultVM
{
	public function __construct(array $row)
	{
		$this->row = $row;
	}

	public function getData() : array
	{
        $details = $this->row['details'];
        $data = [
            'token' => $this->row['token'],
            'user'  => [
                'id' => $this->row['data']['userId'],
                'firstname' => trim($details['firstname']),
                'lastname' => trim($details['lastname']),
                'roles' => $this->row['data']['roles'],
                'email'=> $details['email']
            ],
            'expiresAt' => $this->row['expiresAt'],
        ];
        return $data;
	}
}