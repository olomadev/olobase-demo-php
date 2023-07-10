<?php

namespace App\Filter;

use Laminas\Validator\NotEmpty;

/**
 * InputFilter which only checks the containing Inputs object data sets
 */
class ObjectInputFilter extends InputFilter
{
    /**
     * @var bool
     */
    protected $isRequired = false;
    /**
     * @var array
     */
    protected $objectMessages;

    /**
     * @var NotEmpty
     */
    protected $notEmptyValidator;

    /**
     * Set if the collection can be empty
     *
     * @param bool $isRequired
     * @return CollectionInputFilter
     */
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get if collection can be empty
     *
     * @return bool
     */
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        $messages = [];
        foreach ($this->getInvalidInput() as $name => $input) {
            $messages[$name] = $input->getMessages();
        }
        if (! empty($this->objectMessages)) {
            $messages = array_values($this->objectMessages);
        }
        // foreach($messages as $key => $val) {
        //     if (! empty($val[NotEmpty::IS_EMPTY])) {
        //         $messages[$key] = $val;
        //     }
        // }
        // die;
        return $messages;
    }

    /**
     * Set data to use when validating and filtering
     *
     * @param  iterable|mixed $data
     *     must be a non-empty iterable in order trigger actual validation, else it is always valid
     * @throws Exception\InvalidArgumentException
     * @return InputFilterInterface
     */
    public function setData($data)
    {
        $values = array_values($data);
        $content = implode("", $values);
        /*
            [id => null]
        */
        if (empty($content)) {  // fix empty id data 
            $data = [];
        } 
        return parent::setData($data ?: []);
    }

    /**
     * Run validation, or return true if the data was empty
     *
     * {@inheritDoc}
     */
    public function isValid($context = null)
    {
        $valid = true;
        if (! empty($this->data)) {
            $this->objectMessages = $this->prepareRequiredValidationFailureMessage();
            $valid = false;
        }
        if (! $this->data) {
            return $valid;
        }
        if ($this->data) {
            return parent::isValid($context);
        }

        return true;
    }

    /**
     * Return a list of filtered values, or null if the data was missing entirely
     * Null is returned instead of an empty array to prevent it being passed to a hydrator,
     *     which would likely cause failures later on in your program
     * Fallbacks for the inputs are not respected by design
     *
     * @return array|null
     */
    public function getValues()
    {
        return $this->data ? parent::getValues() : null;
    }

    /**
     * Set the NotEmpty validator to use for failed "required" validations.
     *
     * This validator will be used to produce a validation failure message in
     * cases where the collection is empty but required.
     *
     * @param NotEmpty $notEmptyValidator
     * @return $this
     */
    public function setNotEmptyValidator(NotEmpty $notEmptyValidator)
    {
        $this->notEmptyValidator = $notEmptyValidator;

        return $this;
    }
    /**
     * Retrieve the NotEmpty validator to use for failed "required" validations.
     *
     * This validator will be used to produce a validation failure message in
     * cases where the collection is empty but required.
     *
     * @return NotEmpty
     */
    public function getNotEmptyValidator()
    {
        if ($this->notEmptyValidator === null) {
            $this->notEmptyValidator = new NotEmpty();
        }

        return $this->notEmptyValidator;
    }

    /**
     * @return array<string, string>
     */
    protected function prepareRequiredValidationFailureMessage()
    {
        $notEmptyValidator = $this->getNotEmptyValidator();
        $templates         = $notEmptyValidator->getOption('messageTemplates');
        $message           = $templates[NotEmpty::IS_EMPTY];
        $translator        = $notEmptyValidator->getTranslator();

        return [
            NotEmpty::IS_EMPTY => $translator
                ? $translator->translate($message, $notEmptyValidator->getTranslatorTextDomain())
                : $message,
        ];
    }
}