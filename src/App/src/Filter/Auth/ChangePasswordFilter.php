<?php

declare(strict_types=1);

namespace App\Filter\Auth;

use App\Filter\InputFilter;
use App\Validator\Db\ResetCodeExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class ChangePasswordFilter extends InputFilter
{
    public function __construct(SimpleCacheInterface $simpleCache)
    {
        $this->simpleCache = $simpleCache;
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
                        'simpleCache' => $this->simpleCache,
                    ]
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
        $this->add([
            'name' => 'newPasswordConfirm',
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
        $this->setData($data);
    }
}
