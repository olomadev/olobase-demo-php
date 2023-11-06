<?php

namespace App\Filter;

use Laminas\Validator\NotEmpty;
use Laminas\InputFilter\OptionalInputFilter;

/**
 * InputFilter which only checks the containing Inputs object data sets
 */
class ObjectInputFilter extends OptionalInputFilter
{
    /**
     * @var array
     */
    protected $objectMessages;

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
    // public function isValid($context = null)
    // {
    //     $valid = true;
    //     if (! empty($this->data)) {
    //         $this->objectMessages = $this->prepareRequiredValidationFailureMessage();
    //         $valid = false;
    //     }
    //     if (! $this->data) {
    //         return $valid;
    //     }
    //     if ($this->data) {
    //         return parent::isValid($context);
    //     }

    //     return true;
    // }


}