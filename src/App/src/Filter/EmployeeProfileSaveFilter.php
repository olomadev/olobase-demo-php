<?php

namespace App\Filter;

use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class EmployeeProfileSaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
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
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'employeeProfiles',
                        'field'   => 'profileId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'profileName',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 50,
                    ],
                ],
            ],
        ]);

        // Set input data
        //
        $this->setData($data);
    }
}