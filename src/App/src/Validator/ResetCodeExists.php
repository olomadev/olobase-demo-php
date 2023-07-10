<?php

namespace App\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * Validate reset code exists
 */
class ResetCodeExists extends AbstractValidator
{
    const RESET_CODE_NOT_EXISTS = 'resetCodeNotExists';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::RESET_CODE_NOT_EXISTS => 'Reset password code is expired or invalid',
    ];

    protected $options = [
        'adapter' => null,
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
        $options = $this->getOptions();
        $cache = $options['adapter'];
        
        if (false == $cache->getItem($value)) {
            $this->error(Self::RESET_CODE_NOT_EXISTS);
            return false;
        }
        return true;
    }
}
