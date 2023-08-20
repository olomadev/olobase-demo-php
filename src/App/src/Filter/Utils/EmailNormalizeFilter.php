<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

/**
 * Converts "sada çsd99.jpg" to "sada çsd99"
 */
class EmailNormalizeFilter extends AbstractFilter
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
        $str = str_replace(['I','İ','ı'], ['i','i','i'], $value);
        $str = mb_strtolower($str);
        $str = str_replace(
            ["ğ", "Ğ", "ç", "Ç", "ş", "Ş", "ü", "Ü", "ö", "Ö", "ı", "İ"],
            ['g',"g",'c','c','s','s','u','u','ö','Ö','i','i'],
            $str
        );
        return $str;
    }
}
