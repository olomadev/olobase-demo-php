<?php

namespace App\Handler\Auth;

class TokenViewModel
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
                'id' => $this->row['id'],
                'firstname' => trim($details['firstname']),
                'lastname' => trim($details['lastname']),
                'email' => trim($details['email']),
                'roles' => $this->row['roles'],
            ],
            'avatar' => $details['avatar'],
            'expiresAt' => $this->row['expiresAt']
        ];
        return $data;
	}
}