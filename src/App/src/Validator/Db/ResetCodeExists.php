<?php

namespace App\Validator\Db;

use Laminas\Validator\Exception;
use Laminas\Validator\AbstractValidator;

/**
 * Check cache key exists in the cache
 */
class ResetCodeExists extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_NO_CODE_EXISTS = 'noResetCodeMatched';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_NO_CODE_EXISTS => 'Your password reset code is incorrect or expired',
    ];

    /**
     * @var options
     */
    protected $options = [
        'simpleCache'  => '',
    ];

    /**
     * Returns true if and only if the password match with $value
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        $simpleCache = $this->getOption('simpleCache');
        /**
         * Check for an adapter being defined. If not, throw an exception.
         */
        if (null === $simpleCache) {
            throw new Exception\RuntimeException('No simple cache object present');
        }
        $valid = true;
        if (false == $simpleCache->get($value)) {
            $valid = false;
            $this->error(self::ERROR_NO_CODE_EXISTS);
        }
        return $valid;
    }
}
