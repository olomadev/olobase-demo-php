<?php

namespace App\ViewModel;

class PermissionsFindOneByIdVM
{
    public function __construct(array $row)
    {
        $this->row = $row;
    }

    public function getData() : array
    {
        $row = $this->row;
        $data = [
            'id' => (string)$row['permId'],
            'moduleName' => (string)$row['moduleName'],
            'route' => (string)$row['route'],
            'method' => (string)$row['method'],
        ];
        return $data;
    }
}