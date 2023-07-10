<?php

namespace App\Filter;

use App\Model\CommonModel;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use App\Filter\EmailNormalizeFilter;
use Laminas\Validator\EmailAddress;
use Laminas\I18n\Validator\PhoneNumber;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class DepartmentSaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        CommonModel $commonModel,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->commonModel = $commonModel;
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $areaCodes = $this->commonModel->findAreaCodeIds();

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'departments',
                        'field'   => 'departmentId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $companyFilter = new ObjectInputFilter();
        $companyFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'companies',
                        'field'   => 'companyId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add($companyFilter, 'companyId');

        $companyFilter = new ObjectInputFilter();
        $companyFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y',
                        'strict' => false,
                    ]
                ],
            ],
        ]);
        $this->add($companyFilter, 'yearId');

        $this->add([
            'name' => 'departmentName',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'subDepartmentName',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerName',
            'required' => false,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerSurname',
            'required' => false,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerPhoneAreaCodeId',
            'required' => false,
            'validators' => [
                [
                   'name' => InArray::class,
                    'options' => [
                        'haystack' => $areaCodes,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerPhone',
            'required' => false,
            'filters' => [
                ['name' => PhoneFilter::class],
            ],
            'validators' => [
                [
                    'name' => PhoneNumber::class,
                    'options' => [
                        'allow_possible' => false,
                        'country' =>  empty($data['managerPhoneAreaCodeId']) ? 'TR' : $data['managerPhoneAreaCodeId'],
                    ]
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerMobileAreaCodeId',
            'required' => false,
            'validators' => [
                [
                   'name' => InArray::class,
                    'options' => [
                        'haystack' => $areaCodes,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerMobile',
            'required' => false,
            'filters' => [
                ['name' => PhoneFilter::class],
            ],
            'validators' => [
                [
                    'name' => PhoneNumber::class,
                    'options' => [
                        'allow_possible' => false,
                        'country' =>  empty($data['managerMobileAreaCodeId']) ? 'TR' : $data['managerMobileAreaCodeId'],
                    ]
                ],
            ],
        ]);
        $this->add([
            'name' => 'managerEmail',
            'required' => false,
            'filters' => [
                ['name' => EmailNormalizeFilter::class],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ]
            ],
        ]);
      
        // render & set input data
        //
        $this->renderInputData($data);
    }
}
