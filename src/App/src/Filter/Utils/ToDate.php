<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

class ToDate extends AbstractFilter
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
            return null;
        }
        $dateString = str_replace(['/','.'], '-', $value);
        $date = date('Y-m-d', strtotime($dateString));
        return $date;
    }
}
