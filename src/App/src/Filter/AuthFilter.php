<?php

namespace App\Filter;

use Laminas\Filter\StringTrim;
use Laminas\Validator\EmailAddress;

class AuthFilter extends InputFilter
{
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
            ],
        ]);
        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
        ]);
        $this->renderInputData($data);
    }
}
