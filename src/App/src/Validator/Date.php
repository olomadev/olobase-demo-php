<?php

namespace App\Validator;

use Laminas\Validator\Date as DateValidator;

/**
 * Validate time e.g: 16:10 24 Hour format
 */
class Time extends DateValidator
{
    const INVALID_TIME_FORMAT = 'invalidTimeFormat';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::INVALID_TIME_FORMAT => 'Invalid time format',
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
        if (false == preg_match("/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/", $value)) {
            $this->error(Self::INVALID_TIME_FORMAT);
            return false;
        }
        $this->setValue($value.':00');
        return true;
    }
}
