<?php

namespace App\Filter;

use App\Validator\FileUpload;
use Laminas\Filter\File\RenameUpload;
use Laminas\Validator\EmailAddress;
use Laminas\InputFilter\InputFilterPluginManager;

class FileUploadFilter extends InputFilter
{
    public function __construct(
        InputFilterPluginManager $filter
    )
    {
        $this->filter = $filter;
    }

    public function setInputData(array $data)
    {
        $this->add([
            'name' => 'file',
            'required' => true,
            'validators' => [
                [
                    'name' => FileUpload::class,
                    'options' => [
                        'allowed_extensions' => ['xlsx'],
                        'max_allowed_upload' => 10485760,  // 10 mega bytes
                        'mime_types' => [
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel'
                        ],
                    ],
                ],
            ],
        ]);

        $this->renderInputData($data);
    }
}
