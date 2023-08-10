<?php
declare(strict_types=1);

namespace App\Utils;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Validator\Translator\TranslatorInterface;

/**
 * This fixes a bug in the Laminas Validator Plugin Manager 
 * 
 * @see https://github.com/laminas/laminas-validator/issues/194
 */
class ValidatorTranslator implements TranslatorInterface
{
    public function __construct(
        private Translator $translator
    ) {
        $this->translator = $translator;
    }

    public function translate(
        $message,
        $textDomain = 'default',
        $locale = null
    ) {
        return $this->translator->translate($message, $textDomain, $locale);
    }
}