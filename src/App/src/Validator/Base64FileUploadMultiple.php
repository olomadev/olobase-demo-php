<?php

namespace App\Validator;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\AbstractValidator;

/**
 * Validate specifications array when we create the consultants
 */
class Base64FileUploadMultiple extends AbstractValidator
{
    const EMPTY_FILE_ID = 'emptyFileId';
    const EMPTY_FILE_CONTENT = 'emptyFileContent';
    const EMPTY_MIME_TYPES_OPTION = 'emptyFileMimeTypesOption';
    const EMPTY_MAX_ALLOWED_UPLOAD_OPTION = 'emptyMaxAllowedUploadOption';
    const INVALID_FILE_CONTENT = 'invalidbinaryContent';
    const INVALID_FILE_MIME_TYPE = 'invalidFileMimeType';
    const MAX_ALLOWED_UPLOAD_SIZE_EXCEED = 'exceedAllowedUploadSize';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::EMPTY_FILE_ID => 'Empty file id',
        Self::EMPTY_FILE_CONTENT => 'Empty file content',
        Self::EMPTY_MAX_ALLOWED_UPLOAD_OPTION => 'Empty "max_allowed_upload" option',
        Self::EMPTY_MIME_TYPES_OPTION => 'Empty file "mime_types" option',
        Self::INVALID_FILE_CONTENT => 'Invalid file content',
        Self::INVALID_FILE_MIME_TYPE => 'Invalid file mime type',
        Self::MAX_ALLOWED_UPLOAD_SIZE_EXCEED => 'Max allowed upload size exceed',
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'max_allowed_upload' => ['options' => 'max_allowed_upload'],
        'mime_types' => ['options' => 'mime_types'],
    ];

    protected $options = [
        'operation' => '',
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
        if (empty($this->options['max_allowed_upload'])) {
            $this->error(Self::EMPTY_MAX_ALLOWED_UPLOAD_OPTION);
            return false;
        }
        if (empty($this->options['mime_types'])) {
            $this->error(Self::EMPTY_MIME_TYPES_OPTION);
            return false;
        }
        $operation = (string)$this->options['operation'];
        $maxAllowedUpload = (int)$this->options['max_allowed_upload'];
        $allowedFileMimeTypes = (array)$this->options['mime_types'];

        // Parse & validate files
        // 
        foreach ($value as $k => $v) {
            if (empty($v['id'])) {
                $this->error(Self::EMPTY_FILE_ID);
                return false;
            }
            // pass binary content control for update and empty data
            // 
            if ($operation == 'update' && empty($v['data'])) {
                return true;
            }
            if (empty($v['data'])) {
                $this->error(Self::EMPTY_FILE_CONTENT);
                return false;
            }
            $value[$k]['data'] = Self::cleanBase64Data($v['data']);
            $binaryContent = base64_decode($value[$k]['data']);
            if (false == $binaryContent) {
                $this->error(Self::INVALID_FILE_CONTENT);
                return false;
            }
            if (strlen($binaryContent) > $maxAllowedUpload) {
                $this->error(Self::MAX_ALLOWED_UPLOAD_SIZE_EXCEED);
                return false;
            }
            // https://packagist.org/packages/league/mime-type-detection
            //
            $detector = new FinfoMimeTypeDetector;
            $realMimeType = $detector->detectMimeTypeFromBuffer($binaryContent);
            if (false == in_array($realMimeType, $allowedFileMimeTypes)) {
                $this->error(Self::INVALID_FILE_MIME_TYPE);
                return false;
            }
            $value[$k]['data'] = $binaryContent;
        };
        return true;
    }

    // strip "data:image/jpeg;base64," base64 code mime type
    // 
    static function cleanBase64Data($value)
    {
        if (strpos($value, ",") > 0) {
            $exp = explode(",", $value);
            return $exp[1];
        }
        return $value;
    }

}
