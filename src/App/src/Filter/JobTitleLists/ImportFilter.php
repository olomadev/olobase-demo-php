<?php

declare(strict_types=1);

namespace App\Filter\JobTitleLists;

use App\Model\CommonModel;
use App\Filter\InputFilter;
use App\Filter\ObjectInputFilter;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;
use Laminas\InputFilter\InputFilterPluginManager;

class ImportFilter extends InputFilter
{
    public function __construct(
        CommonModel $commonModel,
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
        $this->commonModel = $commonModel;
    }

    public function setInputData(array $data)
    {
        $years = $this->commonModel->findYearIds();

        $objectFilter = $this->filter->get(ObjectInputFilter::class);
        $objectFilter->add([
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
        $this->add($objectFilter, 'yearId');

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
