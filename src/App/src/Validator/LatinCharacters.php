<?php

namespace App\Validator;

use Laminas\Validator\AbstractValidator;

class LatinCharacters extends AbstractValidator
{
    const INVALID_CHARACTERS = 'onlyLatinCharactersAccepts';

    /**
     * @var array
     */
    protected $messageTemplates = [
        Self::INVALID_CHARACTERS => 'Only Latin characters can be used in this field',
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
        $str = str_replace(['@', '.'], '', $value);
        if (false == preg_match('#^\p{Latin}+$#', $str)) {
            $this->error(Self::INVALID_CHARACTERS);
            return false;
        }
        $this->setValue($value);
        return true;
    }
}
