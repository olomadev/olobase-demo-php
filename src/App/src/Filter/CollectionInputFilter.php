<?php

namespace App\Filter;

use Laminas\InputFilter\CollectionInputFilter as LaminasCollectionInputFilter;

class CollectionInputFilter extends LaminasCollectionInputFilter
{
    /**
     * Get the input filter used when looping the data
     *
     * @return BaseInputFilter
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->setInputFilter(new InputFilter());
        }

        return $this->inputFilter;
    }

    /**
     * {@inheritdoc}
     * @param mixed $context Ignored, but present to retain signature compatibility.
     */
    public function isValid($context = null)
    {
        $this->collectionMessages = [];
        $inputFilter              = $this->getInputFilter();
        $valid                    = true;

        if ($this->getCount() < 1 && $this->isRequired) {
            $this->collectionMessages[] = $this->prepareRequiredValidationFailureMessage();
            $valid                      = false;
        }

        $dataCount = $this->data !== null ? count($this->data) : 0;
        if ($dataCount < $this->getCount()) {
            $valid = false;
        }

        if (! $this->data) {
            $this->clearValues();
            $this->clearRawValues();

            return $valid;
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($this->data as $key => $data) {
            /** @psalm-suppress MixedArgument */
            $inputFilter->setData($data);

            if (null !== $this->validationGroup) {
                $inputFilter->setValidationGroup($this->validationGroup[$key]);
            }

            if ($inputFilter->isValid($context)) {
                $this->validInputs[$key] = $inputFilter->getValidInput();
            } else {
                $valid                          = false;
                $this->collectionMessages[$key] = $inputFilter->getMessages();
                $this->invalidInputs[$key]      = $inputFilter->getInvalidInput();
            }

            $this->collectionValues[$key]    = $inputFilter->getValues();
            $this->collectionRawValues[$key] = $inputFilter->getRawValues();
        }

        return $valid;
    }
    
}
