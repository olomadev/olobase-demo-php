<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

/**
 * Create column name
 */
class MbColumnNameFilter extends AbstractFilter
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
        $value = str_replace(" ", '', $value);
        $str = str_replace(['I','İ'], ['ı','i'], $value);
        $str = mb_strtolower($str);
        $str = str_replace(['ğ','ü','ç','ş','ı','ö'], ['g','u','c','s','i','o'], $str);
        return $str;
    }
}
