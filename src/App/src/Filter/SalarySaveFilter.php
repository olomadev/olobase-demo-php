<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\StringLength;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\I18n\Validator\IsFloat;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class SalarySaveFilter extends InputFilter
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
        $months = $this->commonModel->findMonthIds();

        $yearId = date("Y");
        if (! empty($data['yearId']['id'])) {
            $yearId = $data['yearId']['id'];
        }
        $paymentTypeParams = $this->commonModel->findPaymentTypeParamIds($data['yearId']['id']);

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'salaries',
                        'field'   => 'salaryId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);

        $paymentTypeParamIdFilter = new ObjectInputFilter();
        $paymentTypeParamIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $paymentTypeParams,
                    ],
                ],
            ],
        ]);
        $this->add($paymentTypeParamIdFilter, 'paymentTypeParamId');

        $employeeIdFilter = new ObjectInputFilter();
        $employeeIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'employees',
                        'field'   => 'employeeId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add($employeeIdFilter, 'employeeId');

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

        $monthIdFilter = new ObjectInputFilter();
        $monthIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $months,
                    ],
                ],
            ],
        ]);
        $this->add($monthIdFilter, 'monthId');

        $this->add([
            'name' => 'amount',
            'required' => true,
            'filters' => [
                ['name' => ToMoney::class],
            ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);

        $this->add([
            'name' => 'day',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 2,
                    ],
                ],
            ],
        ]);

        // Set input data
        //
        $this->renderInputData($data);
    }
}
