<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid;
use Laminas\Validator\Date;
use Laminas\Validator\InArray;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class PayrollSchemeSaveFilter extends InputFilter
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
        $companies = $this->commonModel->findCompanyIds();
        $workplaces = $this->commonModel->findWorkplaceIds();

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'payrollScheme',
                        'field'   => 'payrollSchemeId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);

        $companyIdFilter = new ObjectInputFilter();
        $companyIdFilter->add([
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

        $this->add([
            'name' => 'schemeName',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 160,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'schemeDescription',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'startDate',
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
            'name' => 'endDate',
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

        // Set input data
        //
        $this->renderInputData($data);
    }
}
