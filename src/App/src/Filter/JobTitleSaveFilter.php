<?php

namespace App\Filter;

use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class JobTitleSaveFilter extends InputFilter
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
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'jobTitles',
                        'field'   => 'jobTitleId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'jobTitleName',
            'required' => true,
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
        $this->renderInputData($data);
    }
}
