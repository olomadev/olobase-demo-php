<?php

declare(strict_types=1);

namespace App\Filter\Departments;

use App\Model\CommonModel;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class SaveFilter extends InputFilter
{
    public function __construct(
        CommonModel $commonModel, 
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->commonModel = $commonModel;
        $this->adapter = $commonModel->getAdapter();
    }

    public function setInputData(array $data)
    {
        $companies = $this->commonModel->findCompanyIds();

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

        $this->add([
            'name' => 'departmentName',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 150,
                    ],
                ],
            ],
        ]);

        // Set input data
        //
        $this->setData($data);
    }
}
