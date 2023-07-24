<?php

declare(strict_types=1);

namespace App\Handler\Account;

class FindMeViewModel
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
        if (! empty($row['avatarImage'])) {
            $data['avatarImage'] = 'data:image/png;base64,'.base64_encode($row['avatarImage']);
        }
        return $data;
	}
}