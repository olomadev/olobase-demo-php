<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Filter\ToInt;
use Laminas\Validator\InArray;
use Laminas\Validator\Date;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class NotificationSaveFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        InputFilterPluginManager $filter,
        CommonModel $commonModel
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
        $this->commonModel = $commonModel;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'notifications',
                        'field'   => 'notifyId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'notifyName',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 160,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'moduleId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $this->add([
            'name' => 'dateId',
            'required' => true,
        ]);
        $this->add([
            'name' => 'days',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $this->add([
            'name' => 'dayType',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['dayBefore', 'dayAfter'],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'sameDay',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $this->add([
            'name' => 'atTime',
            'required' => false,
        ]);
        $this->add([
            'name' => 'notifyType',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['email'],
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'message',
            'required' => false,
        ]);
        $this->add([
            'name' => 'active',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $this->add([
            'name' => 'users',
            'required' => true,
        ]);
        // render & set input data
        //
        $this->renderInputData($data);
    }
}
