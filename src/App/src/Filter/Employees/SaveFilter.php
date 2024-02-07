<?php

declare(strict_types=1);

namespace App\Filter\Employees;

use App\Model\CommonModel;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Filter\CollectionInputFilter;
use App\Filter\Utils\ToDate;
use App\Filter\Utils\ToBlob;
use App\Filter\Utils\MbUcFirstFilter;
use App\Validator\BlobFileUploadMultiple;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\InputFilter\InputFilterPluginManager;

class SaveFilter extends InputFilter
{
    public function __construct(
        CommonModel $commonModel, 
        InputFilterPluginManager $filter
    )
    {
        $this->commonModel = $commonModel;
        $this->filter = $filter;
        $this->adapter = $commonModel->getAdapter();
    }

    public function setInputData(array $data)
    {
        $companies = $this->commonModel->findCompanyIds();
        $departments = $this->commonModel->findDepartmentIds();
        $jobTitles = $this->commonModel->findJobTitleIds();
        $employeeGrades = $this->commonModel->findEmployeeGradeIds();

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'employees',
                        'field'   => 'employeeId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'employeeNumber',
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
                        'max' => 100,
                    ],
                ],           
            ],
        ]);
        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $companies,
                    ],
                ],
            ],
        ]);
        $this->add($objectFilter, 'companyId');

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $departments,
                    ],
                ],
            ],
        ]);
        $this->add($objectFilter, 'departmentId');

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $jobTitles,
                    ],
                ],
            ],
        ]);
        $this->add($objectFilter, 'jobTitleId');

        $this->add([
            'name' => 'name',
            'required' => true,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 60,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'surname',
            'required' => true,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 60,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'employmentStartDate',
            'required' => false,
            'filters' => [
                ['name' => ToDate::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                        'strict' => true,
                    ]
                ],
            ],
        ]);
        $this->add([
            'name' => 'employmentEndDate',
            'required' => false,
            'filters' => [
                ['name' => ToDate::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                        'strict' => true,
                    ]
                ],
            ],
        ]);
        $this->add([
            'name' => 'files',
            'required' => false,
            'filters' => [
                ['name' => ToBlob::class],
            ],
            'validators' => [
                [
                    'name' => BlobFileUploadMultiple::class,
                    'options' => [
                        'operation' => HTTP_METHOD == 'POST' ? 'create' : 'update',
                        'max_allowed_upload' => 2097152,  // 2 mega bytes
                        'mime_types' => [
                            'image/png', 'image/jpeg', 'image/gif',
                        ],
                    ],
                ],
            ],
        ]);
        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $employeeGrades,
                    ],
                ],
            ],
        ]);
        $this->add($objectFilter, 'gradeId');

        // Children Input filter
        //
        $collection = $this->filter->get(CollectionInputFilter::class);
        $inputFilter = $this->filter->get(InputFilter::class);
        $inputFilter->add([
            'name' => 'childId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'childName',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'childBirthdate',
            'required' => true,
            'filters' => [
                ['name' => ToDate::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                        'strict' => true,
                    ]
                ],
            ],
        ]);
        $collection->setInputFilter($inputFilter);
        $this->add($collection, 'employeeChildren');

        // Set input data
        //
        $this->setData($data);
    }
}
