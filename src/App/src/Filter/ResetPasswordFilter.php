<?php

namespace App\Filter;

use Laminas\Filter\StringTrim;
use App\Validator\ResetCodeExists;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class ResetPasswordFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        StorageInterface $cache,
        InputFilterPluginManager $filter
    )
    {
        $this->cache = $cache;
        $this->filter = $filter;
        $this->adapter = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'resetCode',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => ResetCodeExists::class,
                    'options' => [
                        'adapter' => $this->cache,
                    ]
                ]
            ], 
        ]);
        $this->add([
            'name' => 'newPassword',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);
        $this->renderInputData($data);
    }
}
