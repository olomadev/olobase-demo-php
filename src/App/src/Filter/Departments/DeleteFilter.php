<?php

declare(strict_types=1);

namespace App\Filter\Departments;

use App\Filter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class DeleteFilter extends InputFilter
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
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
                        'table'   => 'departments',
                        'field'   => 'departmentId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
       
        $this->setData($data);
    }
}
