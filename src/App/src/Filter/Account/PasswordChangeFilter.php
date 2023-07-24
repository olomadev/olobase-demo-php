<?php

declare(strict_types=1);

namespace App\Filter\Account;

use App\Filter\InputFilter;
use Laminas\Filter\StringTrim;
use App\Validator\Db\OldPasswordMatch;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class PasswordChangeFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
        $this->user = $this->getUser();
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'oldPassword',
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
                    'name' => OldPasswordMatch::class,
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
