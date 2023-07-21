<?php

namespace App\Filter;

use Laminas\Validator\Uuid;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class RoleDeleteFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter
    )
    {
        $this->adapter  = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'userId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
       
        $this->renderInputData($data);
    }
}
