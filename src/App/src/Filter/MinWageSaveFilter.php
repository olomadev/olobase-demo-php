<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\I18n\Validator\IsFloat;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class MinWageSaveFilter extends InputFilter
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

        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'minWage',
                        'field'   => 'wageId',
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
            'name' => 'daily',
            'required' => true,
            'filters' => [
                ['name' => ToMoney::class],
            ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);

        $this->add([
            'name' => 'monthly',
            'required' => true,
            'filters' => [
                ['name' => ToMoney::class],
            ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);

        // Set input data
        //
        $this->renderInputData($data);
    }
}
