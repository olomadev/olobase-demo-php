<?php

declare(strict_types=1);

namespace App\Filter\Files;

use App\Filter\InputFilter;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class ReadFileFilter extends InputFilter
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'fileId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'files',
                        'field'   => 'fileId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'tableName',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['employeeFiles'],
                    ],
                ],
            ],
        ]);
       
        $this->setData($data);
    }
}
