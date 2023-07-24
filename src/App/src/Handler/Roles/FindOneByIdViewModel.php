<?php

declare(strict_types=1);

namespace App\Handler\Roles;

class FindOneByIdViewModel
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
            'rolePermissions' => (array)$row['rolePermissions'],
        ];
        return $data;
    }
}