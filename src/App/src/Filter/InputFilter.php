<?php

namespace App\Filter;

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
        //
        // method override start
        //
        return parent::add($input, $name);
        
    }

}
