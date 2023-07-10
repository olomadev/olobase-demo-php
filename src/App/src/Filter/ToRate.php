<?php

namespace App\Filter;

use Laminas\Filter\AbstractFilter;

/**
 * "%" yüzdeyi çıkarıp sayıyı integer yapıyoruz
 */
class ToRate extends AbstractFilter
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
        /**
         * Remove "%" extensions
         */
        $exp = explode(" ", $value);
        $value = trim($exp[0]);
        $value = trim($value);
        return (int)$value;
    }
}
