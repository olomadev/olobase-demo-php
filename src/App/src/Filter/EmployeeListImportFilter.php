<?php

namespace App\Filter;

use App\Model\CommonModel;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;

class EmployeeListImportFilter extends InputFilter
{
    public function __construct(
        AdapterInterface $adapter,
        CommonModel $commonModel,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
        $this->commonModel = $commonModel;
    }

    public function setInputData(array $data)
    {
        $years = $this->commonModel->findYearIds();

        $yearIdFilter = new ObjectInputFilter();
        $yearIdFilter->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => $years,
                    ],
                ],
            ],
        ]);
        $this->add($yearIdFilter, 'yearId');

        $this->add([
            'name' => 'listName',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 150,
                    ],
                ],
            ],
        ]);

        // Set input data
        //
        $this->renderInputData($data);
    }
}
