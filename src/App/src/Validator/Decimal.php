<?php

namespace App\Validator;

use Laminas\Validator\AbstractValidator;

class Decimal extends AbstractValidator
{
    const INVALID_TIME_FORMAT = 'invalidMoneyFormat';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::INVALID_TIME_FORMAT => 'Invalid decimal format',
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
        /**
         * Front end hiçbir zaman para değerlerini 20,7 gibi bir rakam gönderirse
         * rakam yanlış hesaplanır bu yüzden 20,70 son iki hane validasyon kontrolü gerekiyor
         */
        if (strpos($value, ',') > 0 OR strpos($value, '.') > 0) {
            $value = str_replace([',','.'], '', $value);
            if (is_numeric($value)) {
                $value = $value / 100;
            }
        }
        $this->setValue((float)$value);
        return true;
    }
}
