<?php

declare(strict_types=1);

namespace App\Filter\JobTitleLists;

use App\Filter\InputFilter;
use App\Validator\FileUpload;

class FileUploadFilter extends InputFilter
{
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

        $this->setData($data);
    }
}
