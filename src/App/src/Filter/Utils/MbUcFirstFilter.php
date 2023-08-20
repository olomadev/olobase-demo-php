<?php

namespace App\Filter\Utils;

use Laminas\Filter\AbstractFilter;

/**
 * Normalize names
 */
class MbUcFirstFilter extends AbstractFilter
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
        $exp = explode(" ", trim($value));
        if (is_array($exp) && count($exp) > 0) {
            $words = array();
            foreach ($exp as $word) {
                $str = str_replace(['I','İ'], ['ı','i'], $word);
                $str = mb_strtolower($str);
                $words[] = $this->ucFirst($str, 'utf-8');
            }
            return implode(" ", $words);
        }
        $str = str_replace(['I','İ'], ['ı','i'], $value);
        $str = mb_strtolower($str);
        $last = $this->ucFirst($str, 'utf-8');
        return $last;
    }

    private function ucFirst($string, $encoding)
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        if ($firstChar == 'i') {
            $firstChar = 'İ';
        }
        $then = mb_substr($string, 1, null, $encoding);
        $result = mb_strtoupper($firstChar, $encoding) . $then;
        $result = str_replace(["A.ş.","V.d.","V.D"], ["A.Ş.", "V.D.","V.D."], $result);
        return $result;
    }
}
