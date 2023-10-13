<?php

namespace App\Filter\Utils;

use function cleanBase64Image;

use Laminas\Filter\AbstractFilter;

class ToBlob extends AbstractFilter
{
    const FILE_MIME_TYPES = [
        'data:application/pdf;base64,',
        'data:image/jpeg;base64,',
        'data:image/gif;base64,',
        'data:image/png;base64,',
        'data:text/plain;base64,',
        'data:application/msword;base64,',
        'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,',
        'data:application/vnd.oasis.opendocument.text;base64,',
    ];
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns the string $value, removing all but digit characters
     *
     * If the value provided is not integer, float or string, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
        $i = 0;
        $newValues = array();
        foreach ((array)$value as $val) {
            if (! empty($val['data'])) {
                //
                // remove mime and base64 prefixes then decode base64
                // 
                $strippedFile = str_replace(Self::FILE_MIME_TYPES, '', $val['data']);
                $val['data'] = base64_decode($strippedFile);
                $newValues[$i] = $val;
            }
            ++$i;
        }
        return $newValues;
    }
}
