<?php

namespace App\Filter;

use Laminas\I18n\Validator\IsFloat;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class ExchangeRateSaveFilter extends InputFilter
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
            'name' => 'usdExchangeRate',
            'required' => true,
            // 'filters' => [
            //     ['name' => ToExchangeRate::class],
            // ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);
        $this->add([
            'name' => 'euroExchangeRate',
            'required' => true,
            // 'filters' => [
            //     ['name' => ToExchangeRate::class],
            // ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);
        $this->add([
            'name' => 'poundExchangeRate',
            'required' => true,
            // 'filters' => [
            //     ['name' => ToExchangeRate::class],
            // ],
            'validators' => [
                ['name' => IsFloat::class],
            ],
        ]);
        $this->renderInputData($data);
    }
}
