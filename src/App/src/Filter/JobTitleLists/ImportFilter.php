<?php

namespace App\Filter\JobTitleLists;

use App\Model\CommonModel;
use App\Filter\ObjectInputFilter;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;

class ImportFilter extends InputFilter
{
    public function __construct(CommonModel $commonModel)
    {
        $this->filter = $filter;
        $this->adapter = $commonModel->getAdapter();
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
        $this->setData($data);
    }
}
