<?php

namespace App\ViewModel;

use function jsonEncode;

class RolesFindOneByIdVM
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
            'roleKey' => (string)$row['roleKey'],
            'roleName' => (string)$row['roleName'],
            'roleLevel' => (string)$row['roleLevel'],
        ];
        $data['rolePermissions'] = jsonEncode($row['rolePermissions']);
        return $data;
    }
}