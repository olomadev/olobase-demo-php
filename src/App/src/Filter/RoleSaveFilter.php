<?php

namespace App\Filter;

use Laminas\Filter\ToInt;
use App\Validator\Db\RecordExists;
use App\Validator\Db\NoRecordExists;
use Laminas\Validator\Uuid;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class RoleSaveFilter extends InputFilter
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
            'name' => 'id',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
                [
                    'name' => HTTP_METHOD == 'POST' ? NoRecordExists::class : RecordExists::class,
                    'options' => [
                        'table'   => 'roles',
                        'field'   => 'roleId',
                        'adapter' => $this->adapter,
                    ]
                ]
            ],
        ]);
        $this->add([
            'name' => 'roleKey',
            'required' => true,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 60,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'roleName',
            'required' => true,
            'filters' => [
                ['name' => MbUcFirstFilter::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $this->add([
            'name' => 'roleLevel',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        // Role Permissions Input filter
        //
        $rolePermissionsCollection = $this->filter->get(CollectionInputFilter::class);
        $rolePermissionsInputFilter = $this->filter->get(InputFilter::class);
        $rolePermissionsInputFilter->add([
            'name' => 'permId',
            'required' => true,
            'validators' => [
                ['name' => Uuid::class],
            ],
        ]);
        $rolePermissionsCollection->setInputFilter($rolePermissionsInputFilter);
        $this->add($rolePermissionsCollection, 'rolePermissions');


        $this->renderInputData($data);
    }
}
