<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

/**
 * Normalize phone inputs
 */
class ToPhone extends AbstractFilter
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
        $str = trim($value);
        $search  = array('(', ')', '-');
        $finalStr = str_replace($search, "", $value);
        return $finalStr;
    }

}
