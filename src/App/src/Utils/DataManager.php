<?php

declare(strict_types=1);

namespace App\Utils;

use ReflectionClass;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @author Ersin Güvenç <eguvenc@gmail.com> - 2022
 *
 * Entity data manager
 */
class DataManager
{
    const ENTITY_OBJECT = 'object';
    const ENTITY_ARRAY = 'array';

    protected $inputFilter;

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function getEntityData(string $schema, $entityParts = array()) : array
    {
        $data = $this->inputFilter->getData();
        $entityReflection = new ReflectionClass($schema);
        $entityProperties = $entityReflection->getProperties();

        $entityData = array();
        $entityArray = array();
        $arrayKeys = array();
        foreach ($entityProperties as $prop) {
            foreach ($entityParts as $key => $entityClass) {
                $name = $prop->getName();
                $classReflection = new ReflectionClass($entityClass);
                $entityType = $entityClass::ENTITY_TYPE;
                $classProps = $classReflection->getProperties();
                // props
                // 
                foreach ($classProps as $entityProperty) {
                    $entityPropName = (string)$entityProperty->getName();
                    if ($name == $entityPropName) {
                        // if we have new password field we don't want to update the hashed password
                        // 
                        if ($name == 'password' && ! empty($data['newPassword'])) {
                            break;
                        }
                        if (array_key_exists($name, $data)) { // if data has the entity element
                            $entityData[$key][$name] = $this->inputFilter->getValue($entityPropName);
                        }
                    }
                    // 
                    $schemaPropertyComment = $prop->getDocComment();

                    // ObjectId support
                    // ["id": "ebf6b935-5bd8-46c1-877b-9c758073f278", "name", "blabala"]
                    // it converts object to string "id"
                    //
                    if (! empty($entityData[$key][$name]['id']) && strpos($schemaPropertyComment, "ObjectId") > 0) {
                        $objectIdValue = $entityData[$key][$name]['id'];
                        $entityData[$key][$name] = $objectIdValue;
                    }
                    // object support
                    //
                    if ($entityType == Self::ENTITY_OBJECT && isset($data[$key][$entityPropName])) {
                        $objectData = $this->inputFilter->getValue($key);
                        $entityData[$key][$entityPropName] = $objectData[$entityPropName];
                    }
                    // array support
                    //
                    if (array_key_exists($key, $data) && $entityType == Self::ENTITY_ARRAY) {
                        $arrayKeys[$key][$entityPropName] = $entityPropName;
                    }
                }
            }
        }

        // fill array data with input value
        // 
        foreach ($arrayKeys as $aKey => $aPropArray) {
            $arrayData = $this->inputFilter->getValue($aKey);
            foreach ($arrayData as $indexKey => $dVal) {
                foreach ($aPropArray as $aPropName) {
                    if (array_key_exists($aPropName, $dVal)) {
                        $entityData[$aKey][$indexKey][$aPropName] = $dVal[$aPropName];
                    }
                }
            }
        }
        return $entityData;
    }
}
