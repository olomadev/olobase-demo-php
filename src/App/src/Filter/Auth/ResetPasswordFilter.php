<?php

declare(strict_types=1);

namespace App\Filter\Auth;

use App\Filter\InputFilter;
use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Db\RecordExists;
use Laminas\Db\Adapter\AdapterInterface;

class ResetPasswordFilter extends InputFilter
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter  = $adapter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'email',
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
        $this->setData($data);
    }
}
