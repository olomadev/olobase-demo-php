<?php

namespace App\Filter;

use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class SendResetPasswordFilter extends InputFilter
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
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => RecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'email',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->renderInputData($data);
    }
}
