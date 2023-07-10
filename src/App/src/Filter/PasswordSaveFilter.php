<?php

namespace App\Filter;

use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class PasswordSaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
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
        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 8,
                        'max' => 16,
                    ],
                ],
            ],
        ]);
      
        $this->renderInputData($data);
    }
}
