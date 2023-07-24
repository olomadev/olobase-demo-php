<?php

declare(strict_types=1);

namespace App\Filter\Account;

use App\Filter\InputFilter;
use App\Filter\ToFile;
use App\Validator\Base64FileUpload;
use Laminas\Filter\StringTrim;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Validator\Uuid;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;

class SaveFilter extends InputFilter
{
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->user = $this->getUser();
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'email',
            'required' => true,
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'useMxCheck' => false,
                    ],
                ],
                [
                    'name' => NoRecordExists::class,
                    'options' => [
                        'table'   => 'users',
                        'field'   => 'email',
                        'exclude' => [
                            'field' => 'userId',
                            'value' => $this->user->getId(),
                        ],
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'firstname',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'lastname',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 120,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'themeColor',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 7,
                        'max' => 7,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'avatarImage',
            'required' => false,
            'filters' => [
                ['name' => ToFile::class],
            ],
            'validators' => [
                [
                    'name' => Base64FileUpload::class,
                    'options' => [
                        'operation' => HTTP_METHOD == 'POST' ? 'create' : 'update',
                        'max_allowed_upload' => 2097152,  // 2 mega bytes
                        'mime_types' => [
                            'image/png', 'image/jpeg', 'image/gif',
                        ],
                    ],
                ]
            ]
        ]);

        // render & set data
        //
        $this->setData($data);
    }
}