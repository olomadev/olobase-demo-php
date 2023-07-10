<?php

namespace App\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * Nokta ve virgülleri silip sayıyı decimal olarak kaydetmek için
 * sayıyı 100 e bölüp decimal e çeviriyoruz.
 */
class ToFile extends AbstractFilter
{
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
        if (empty($value)) {
            return $value;
        }
        // strip "data:image/jpeg;base64," base64 code mime type
        // 
        $patterns = [
            'data:application/pdf;base64,',
            'data:image/jpeg;base64,',
            'data:image/gif;base64,',
            'data:image/png;base64,',
            'data:text/plain;base64,',
            'data:application/msword;base64,',
            'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,',
            'data:application/vnd.oasis.opendocument.text;base64,',
        ];
        $value = str_replace($patterns, '', $value);
        return $value;
    }
}
