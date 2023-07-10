<?php

namespace App\Filter;

use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\InputFilter\OptionalInputFilter;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class WorkplaceSaveFilter extends InputFilter
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
                        'table'   => 'workplaces',
                        'field'   => 'workplaceId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $companyFilter = new OptionalInputFilter();
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
        
        // $this->add(
        //     $this->getFactory()->createInputFilter(
        //         [
        //             'type' => OptionalInputFilter::class,
        //             [
        //                 'name'     => 'id',
        //                 'required' => true,
        //                 'validators' => [
        //                     ['name' => Uuid::class],
        //                     [
        //                         'name' => RecordExists::class,
        //                         'options' => [
        //                             'table'   => 'companies',
        //                             'field'   => 'companyId',
        //                             'adapter' => $this->adapter,
        //                         ]
        //                     ]
        //                 ],
        //             ],
        //         ]
        //     ),
        //     'companyId'
        // );

        $this->add([
            'name' => 'workplaceName',
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
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'registrationNumber',
            'required' => false,
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
        $this->add([
            'name' => 'address',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 255,
                    ],
                ],
            ],
        ]);
        // Set input data
        //
        $this->setData($data);
    }
}
