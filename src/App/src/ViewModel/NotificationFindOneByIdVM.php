<?php

namespace App\ViewModel;

class NotificationFindOneByIdVM
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
            'notifyName' => (string)$row['notifyName'],
            'moduleId' => (string)$row['moduleId'],
            'dateId' => (string)$row['dateId'],
            'days' => (int)$row['days'],
            'dayType' => (string)$row['dayType'],
            'sameDay' => (int)$row['sameDay'],
            'atTime' => substr($row['atTime'], 0, -3),
            'notifyType' => (string)$row['notifyType'],
            'message' => (string)$row['message'],
            'active' => (int)$row['active'],
            'createdAt' => (string)$row['createdAt'],
        ];
        $users = array();
        foreach ($row['users'] as $user) {
            $users[] = $user['id'];
        }
        $data['users'] = $users;
		return $data;
	}
}