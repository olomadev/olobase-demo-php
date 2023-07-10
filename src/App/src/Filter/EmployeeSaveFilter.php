<?php

namespace App\Filter;

use App\Model\CommonModel;
use App\Validator\TCKN;
use App\Filter\PhoneFilter;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Validator\Iban;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Laminas\I18n\Validator\IsFloat;
use Laminas\I18n\Validator\PhoneNumber;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class EmployeeSaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        CommonModel $commonModel,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
        $this->commonModel = $commonModel;
    }

    public function setInputData(array $data)
    {
        $years = $this->commonModel->findYearIds();
        $workplaces = $this->commonModel->findWorkplaceIds();
        $employeeTypes = $this->commonModel->findEmployeeTypeIds();
        $employeeGrades = $this->commonModel->findEmployeeGradeIds();
        $employeeProfiles = $this->commonModel->findEmployeeProfileIds();
        $disabilities = $this->commonModel->findDisabilityIds();
        $jobTitles = $this->commonModel->findJobTitleIds();
        $costCenters = $this->commonModel->findCostCenterIds();

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
        $yearIdFilter = new ObjectInputFilter();
        $yearIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $years,
                    ],
                ],
            ],
        ]);
        $this->add($yearIdFilter, 'yearId');

        $listIdFilter = new ObjectInputFilter();
        $listIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'employees',
                        'field'   => 'listId',
                        'adapter' => $this->adapter,
                    ]
                ] 
            ],
        ]);
        $this->add($listIdFilter, 'listId');

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

        $companyIdFilter = new ObjectInputFilter();
        $companyIdFilter->add([
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
        $this->add($companyIdFilter, 'companyId');

        $workplaceIdFilter = new ObjectInputFilter();
        $workplaceIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $workplaces,
                    ],
                ],
            ],
        ]);
        $this->add($workplaceIdFilter, 'workplaceId');

        $jobTitleIdFilter = new ObjectInputFilter();
        $jobTitleIdFilter->add([
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
        $this->add($jobTitleIdFilter, 'jobTitleId');

        $employeeProfileFilter = new ObjectInputFilter();
        $employeeProfileFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $employeeProfiles,
                    ],
                ],
            ],
        ]);
        $this->add($employeeProfileFilter, 'employeeProfile');

        $constCenterIdFilter = new ObjectInputFilter();
        $constCenterIdFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $costCenters,
                    ],
                ],
            ],
        ]);
        $this->add($constCenterIdFilter, 'costCenterId');

        $departmentIdFilter = new ObjectInputFilter();
        $departmentIdFilter->add([
            'name' => 'id',
            'required' => false,
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
        $this->add($departmentIdFilter, 'departmentId');

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
            'name' => 'tckn',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            // 'validators' => [
            //     ['name' => TCKN::class],
            // ],
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

        $employeeTypeIdFilter = new ObjectInputFilter();
        $employeeTypeIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $employeeTypes,
                    ],
                ],
            ],
        ]);
        $this->add($employeeTypeIdFilter, 'employeeTypeId');

        $gradeIdFilter = new ObjectInputFilter();
        $gradeIdFilter->add([
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
        $this->add($gradeIdFilter, 'gradeId');

        $disabilityIdFilter = new ObjectInputFilter();
        $disabilityIdFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $disabilities,
                    ],
                ],
            ],
        ]);
        $this->add($disabilityIdFilter, 'disabilityId');

        // Make group data Traversable
        // 
        if (! empty($data['groups'][0])) {
            $data['groups'] = array_map(function($item) { return ['id' => $item['id']]; }, $data['groups']);
        }
        $groupsCollection = $this->filter->get(CollectionInputFilter::class);
        $groupsInputFilter = $this->filter->get(InputFilter::class);
        $groupsInputFilter->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $groupsCollection->setInputFilter($groupsInputFilter);
        $this->add($groupsCollection, 'groups');

        // Set input data
        //
        $this->renderInputData($data);
    }
}
