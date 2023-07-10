<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class EmployeeListSaveFilter extends InputFilter
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
        
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'employeeList',
                        'field'   => 'employeeListId',
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

        $this->add([
            'name' => 'listName',
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
                        'max' => 150,
                    ],
                ],           
            ],
        ]);

        // Set input data
        //
        $this->renderInputData($data);
    }
}
