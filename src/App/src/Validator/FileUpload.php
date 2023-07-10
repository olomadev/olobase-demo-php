<?php

namespace App\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\AbstractValidator;

/**
 * Validate specifications array when we create the consultants
 */
class FileUpload extends AbstractValidator
{
    const FILE_IS_NOT_PROVIDED = 'fileIsNotProvided';
    const EMPTY_FILE_ID = 'emptyFileId';
    const EMPTY_FILE_CONTENT = 'emptyFileContent';
    const EMPTY_MIME_TYPES_OPTION = 'emptyFileMimeTypesOption';
    const EMPTY_ALLOWED_EXTENSIONS_OPTION = 'emptyAllowedExtensionsOption';
    const EMPTY_MAX_ALLOWED_OPTION = 'emptyMaxAllowedUploadOption';
    const INVALID_FILE_MIME_TYPE = 'invalidFileMimeType';
    const MAX_ALLOWED_UPLOAD_SIZE_EXCEED = 'exceedAllowedUploadSize';
    const FILE_FORMAT_IS_NOT_ALLOWED = 'fileFormatIsNotAllowed';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::FILE_IS_NOT_PROVIDED => 'File is not provided',
        Self::EMPTY_FILE_ID => 'Empty file id',
        Self::EMPTY_FILE_CONTENT => 'Empty file content',
        Self::EMPTY_ALLOWED_EXTENSIONS_OPTION => 'Empty "allowed_extensions" option',
        Self::EMPTY_MAX_ALLOWED_OPTION => 'Empty "max_allowed_upload" option',
        Self::EMPTY_MIME_TYPES_OPTION => 'Empty file "mime_types" option',
        Self::INVALID_FILE_MIME_TYPE => 'Invalid file mime type',
        Self::MAX_ALLOWED_UPLOAD_SIZE_EXCEED => 'Max allowed upload size exceed',
        Self::FILE_FORMAT_IS_NOT_ALLOWED => 'This file format is not allowed',
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'allowed_extensions' => ['options' => 'allowed_extensions'],
        'max_allowed_upload' => ['options' => 'max_allowed_upload'],
        'mime_types' => ['options' => 'mime_types'],
    ];

    protected $options = [
        'allowed_extensions' => '',
        'mime_types'  => '',
        'max_allowed_upload'  => '',  // default 10 mega byte
    ];

    /**
     * Returns true if and only if $value meets the validation requirements.
     *
     * @param mixed $value
     *
     * @return bool
     *
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (empty($this->options['allowed_extensions'])) {
            $this->error(Self::EMPTY_ALLOWED_EXTENSIONS_OPTION);
            return false;
        }
        if (empty($this->options['max_allowed_upload'])) {
            $this->error(Self::EMPTY_MAX_ALLOWED_OPTION);
            return false;
        }
        if (empty($this->options['mime_types'])) {
            $this->error(Self::EMPTY_MIME_TYPES_OPTION);
            return false;
        }
        $allowedExtensions = (array)$this->options['allowed_extensions'];
        $maxAllowedUpload = (int)$this->options['max_allowed_upload'];
        $allowedFileMimeTypes = (array)$this->options['mime_types'];

        if (empty($value['name'])) {
            $this->error(Self::FILE_IS_NOT_PROVIDED);
            return false;
        }
        $ext = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, $allowedExtensions)) {
            $this->error(Self::FILE_FORMAT_IS_NOT_ALLOWED);
            return false;
        }
        if (! in_array($value['type'], $allowedFileMimeTypes)) {
            $this->error(Self::INVALID_FILE_MIME_TYPE);
            return false;
        }
        if ($value['size'] > $maxAllowedUpload) {
            $this->error(Self::MAX_ALLOWED_UPLOAD_SIZE_EXCEED);
            return false;
        }
        return true;
    }


}
