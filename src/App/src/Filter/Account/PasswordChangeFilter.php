<?php

declare(strict_types=1);

namespace App\Filter\Account;

use App\Filter\InputFilter;
use App\Validator\Db\CurrentPasswordMatch;
use Laminas\Filter\StringTrim;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;

class PasswordChangeFilter extends InputFilter
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->user = $this->getUser();
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'currentPassword',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 8,
                        'max' => 16,
                    ],
                ],
                [
                    'name' => CurrentPasswordMatch::class,
                    'options' => [
                        'userId' => $this->user->getId(),
                        'adapter' => $this->adapter,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'newPassword',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 8,
                        'max' => 16,
                    ],
                ],
            ],
        ]);

        // Set input data
        //
        $this->setData($data);
    }
}
