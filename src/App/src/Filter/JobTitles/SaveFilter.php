<?php

namespace App\Filter\JobTitles;

use App\Model\CommonModel;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;

class SaveFilter extends InputFilter
{
    public function __construct(
        CommonModel $commonModel, 
        AdapterInterface $adapter
    )
    {
        $this->commonModel = $commonModel;
        $this->adapter  = $adapter;
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
        $objectFilter = new ObjectInputFilter();
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

        $this->setData($data);
    }
}
