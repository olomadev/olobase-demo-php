<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

/**
 * Nokta ve virgülleri silip sayıyı decimal olarak kaydetmek için
 * sayıyı 100 e bölüp decimal e çeviriyoruz.
 */
class ToMoney extends AbstractFilter
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
            return (float)0;
        }
        /**
         * Remove "TL","USD","EUR" extensions
         */
        $exp = explode(" ", $value);
        $value = trim($exp[0]);
        $value = trim($value);
        /**
         * Front end hiçbir zaman para değerlerini 20,7 gibi bir rakam göndermemeli,
         * yoksa yanlış hesaplanır doğrusu 20,70 formatlayıp göndermeli
         * ya da 20.70
         */
        if (strpos($value, ',') > 0 OR strpos($value, '.') > 0) {
            $value = str_replace([',','.'], '', $value);
            if (is_numeric($value)) {
                $value = $value / 100;
            }
        }
        return (float)$value;
    }
}
