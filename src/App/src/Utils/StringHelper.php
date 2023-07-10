<?php

namespace App\Utils;

class StringHelper
{
    public static function normalizeEmail($email) {
        $value = trim($email);
        $str = str_replace(['I','İ','ı'], ['i','i','i'], $value);
        $str = mb_strtolower($str);
        $str = str_replace(
            ["ğ", "Ğ", "ç", "Ç", "ş", "Ş", "ü", "Ü", "ö", "Ö", "ı", "İ"],
            ['g',"g",'c','c','s','s','u','u','ö','Ö','i','i'],
            $str
        );
        return $str;
    }
    // https://github.com/epigra/tckimlik/blob/master/src/TcKimlik.php
    // 
    public static function verifyTc($input)
    {
        $tcno = $input;
        if (is_array($input) && !empty($input['tcno'])) {
            $tcno = $input['tcno'];
        }

        if (is_array($tcno)) {
            $inputKeys = array_keys($tcno);
            $tcno = $input[$inputKeys[0]];
        }

        if (!preg_match('/^[1-9]{1}[0-9]{9}[0,2,4,6,8]{1}$/', $tcno)) {
            return false;
        }
        
        $odd = $tcno[0] + $tcno[2] + $tcno[4] + $tcno[6] + $tcno[8];
        $even = $tcno[1] + $tcno[3] + $tcno[5] + $tcno[7];
        $digit10 = ($odd * 7 - $even) % 10;
        $total = ($odd + $even + $tcno[9]) % 10;

        if ($digit10 != $tcno[9] ||  $total != $tcno[10]) {
            return false;
        }
        return true;
    }

    public static function filter($value)
    {
        $exp = explode(" ", trim($value));
        if (is_array($exp) && count($exp) > 0) {
            $words = array();
            foreach ($exp as $word) {
                $str = str_replace(['I','İ'], ['ı','i'], $word);
                $str = mb_strtolower($str);
                $words[] = ucFirstWord($str, 'utf-8');
            }
            return implode(" ", $words);
        }
        $str = str_replace(['I','İ'], ['ı','i'], $value);
        $str = mb_strtolower($str);
        $last = ucFirstWord($str, 'utf-8');
        return $last;
    }

    public static function ucFirstWord($string, $encoding)
    {
        $firstChar = mb_substr($string, 0, 1, $encoding);
        if ($firstChar == 'i') {
            $firstChar = 'İ';
        }
        $then = mb_substr($string, 1, null, $encoding);
        $result = mb_strtoupper($firstChar, $encoding) . $then;
        return $result;
    }
}