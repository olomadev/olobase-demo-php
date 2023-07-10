<?php

namespace App\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * Validate Turkish identity number
 */
class TCKN extends AbstractValidator
{
    const INVALID_TCKN_FORMAT = 'invalidTcknFormat';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::INVALID_TCKN_FORMAT => 'Invalid TCKN format',
    ];

    /**
     * Returns true if and only if $value meets the validation requirements.
     *
     * @param mixed $value
     *
     * @return bool
     *
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (empty($value)) {
            return false;
        }
        // https://github.com/epigra/tckimlik/blob/master/src/TcKimlik.php
        // 
        $tcno = $value;
        if (is_array($value)) {
            $tcno = $value['tcno'];
        }
        if (is_array($tcno)) {
            $valueKeys = array_keys($tcno);
            $tcno = $value[$valueKeys[0]];
        }
        // if (!preg_match('/^[1-9]{1}[0-9]{9}[0,2,4,6,8]{1}$/', $tcno)) {
        //     return false;
        // }
        $odd = $tcno[0] + $tcno[2] + $tcno[4] + $tcno[6] + $tcno[8];
        $even = $tcno[1] + $tcno[3] + $tcno[5] + $tcno[7];
        $digit10 = ($odd * 7 - $even) % 10;
        $total = ($odd + $even + $tcno[9]) % 10;

        if ($digit10 != $tcno[9] ||  $total != $tcno[10]) {
            return false;
        }
        return true;
    }
}