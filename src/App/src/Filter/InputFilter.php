<?php

namespace App\Filter;

use function jsonDecode;

use Traversable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\InputFilter\InputInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilter as LaminasInputFilter;

class InputFilter extends LaminasInputFilter
{
    protected $user;
    protected $request;
    protected $filter;
    protected $adapter;
    protected $collectionNames = array();

    // protected $elements = array();

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setInputData(array $data)
    {
        $this->setData($data);
    }
    
    /**
     * Returns to input data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Add an input to the input filter
     *
     * @param  array|Traversable|InputInterface|InputFilterInterface $input
     * @param  null|string $name
     * @return InputFilter
     */
    public function add($input, $name = null)
    {
        // Ersin: let's store collection data
        // 
        // if ($input instanceof CollectionInputFilter) {
        //     $this->collectionNames[$name] = $name; 
        // }
        // method override start
        //
        return parent::add($input, $name);

        // if (is_array($input)) {  // for InputFilter object
        //     $inputName = $input['name'];
        //     //
        //     // password alanları hariç element tablosundan gelenleri kullan.
        //     // 
        //     if (isset($this->elements[$inputName]) AND false == in_array($inputName,
        //         [
        //             'password',
        //             'oldPassword',
        //             'newPassword',
        //         ]
        //     )) {
        //         $input['required'] = (bool)$this->elements[$inputName];
        //     }
        // }
        // if (($input instanceof CollectionInputFilter || $input instanceof ObjectInputFilter) && isset($this->elements[$name])
        // ) {
        //     $input->setIsRequired((bool)$this->elements[$name]);
        // }
        // // method override end
        // //
        // if (is_array($input) OR ($input instanceof Traversable && ! $input instanceof InputFilterInterface)
        // ) {
        //     $factory = $this->getFactory();
        //     $input = $factory->createInput($input);
        // }
        // return parent::add($input, $name);
    }
}
