<?php

namespace AuctioCore\Api;

use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage AS AuctioLocaleMessage;
use AuctioCore\Api\Auctio\Entity\Custom\DateTime AS AuctioDateTime;

abstract class Base implements BaseInterface
{

    public function __construct($data = array()) {
        if(!empty($data)) {
            $this->populate((object)$data);
        }
    }
    /**
     * @var array
     */
    protected static $populateProperties;

    /**
     * @var array
     */
    protected static $exportProperties;

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @return string
     */
    public function encode() {
        if(!isset(self::$exportProperties[get_called_class()])) {
            $reflectionObject = new \ReflectionObject($this);

            foreach($reflectionObject->getProperties() as $property) {
                if(stripos($property->getDocComment(), '@ReadOnly') === false) {
                    self::$exportProperties[get_called_class()][] = $property->getName();
                }
            }
        }

        return json_encode(
            array_intersect_key($this->formatVars(get_object_vars($this)), array_flip(self::$exportProperties[get_called_class()]))
        );
    }

    /**
     * Parse vars so that it can be propery json encoded
     * @param $array vars
     * @return array parsed vars
     * @throws \Exception
     */
    protected function formatVars(array $vars) {
        $formattedVars = array();
        foreach($vars as $varName => $var) {
            if(is_object($var) && !($var instanceof \stdClass) ) {
                if($var instanceof Base) {
                    $var = json_decode($var->encode());
                }
                elseif($var instanceof \DateTime) {
                    $var = $var->format(\Datetime::ISO8601);
                } elseif(method_exists($var, '__toString')) {
                    $var = (string)$var;
                } else {
                    throw new \Exception('Cannot convert object of type ' . get_class($var) . ' to string');
                }
            }
            $formattedVars[$varName] = $var;
        }

        return $formattedVars;
    }

    /**
     * Loop over all properties and set them in the entity
     * @param \stdClass $data
     * @return self
     */
    public function populate($data) {
        if ($data === null || empty($data)) {
            return $this;
        }

        if(!($data instanceof \stdClass)) {
            throw new \InvalidArgumentException('$data should be instance of stdClass');
        }

        if(!isset(static::$populateProperties[get_called_class()])) {
            $reflectionObject = new \ReflectionObject($this);

            // loop over all properties looking for custom datatypes
            foreach($reflectionObject->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $matches = array();
                // look for @var Classname
                if(preg_match('/@var\s+([a-zA-Z\\\\]+)(\[])?/s', $property->getDocComment(), $matches) ) {
                    // found type hint

                    // look for basic type
                    if(in_array($matches[1], Defaults::$basicTypes)) {
                        static::$populateProperties[get_called_class()][$property->getName()] = $matches[1];
                        continue;
                    }

                    // found a custom data type
                    $className = substr(__NAMESPACE__, 0 , strrpos(__NAMESPACE__, '\\')) . '\\' . $matches[1];
                    if(!class_exists($className)) {
                        throw new \Exception('Could not find type:' . $className);
                    }
                    if(!is_subclass_of($className, '\AuctioCore\Api\BaseInterface')) {
                        throw new \Exception('Type is not known:' . $className);
                    }
                    static::$populateProperties[get_called_class()][$property->getName()] = $className;
                } else {
                    static::$populateProperties[get_called_class()][$property->getName()] = 'string';
                }
            }
        }

        // Set values inside entity and populate if custom entity
        foreach(get_object_vars($data) as $name => $value) {
            // only set properties that exist (are public)
            if(isset(self::$populateProperties[get_called_class()][$name])) {
                $type = self::$populateProperties[get_called_class()][$name];

                // if default type, parse it to that type (unless stdClass)
                if(in_array($type, Defaults::$basicTypes)) {
                    if(!( $value instanceof \stdClass) ) {
                        if (!is_null($value)) {
                            settype($value, $type);
                        }
                    }
                    $this->$name = $value;
                }  else {
                    // This is a custom value. If a list, we loop over each item
                    $typeObject = new $type;
                    if(is_array($value)) {
                        if($typeObject instanceof AuctioLocaleMessage) {
                            $this->$name = $typeObject->populate($value);
                        } elseif($typeObject instanceof AuctioDateTime) {
                            $this->$name = $typeObject->populate($value);
                        } else {
                            $this->$name = array();
                            foreach ($value as $keyElement => $valueElement) {
                                array_push($this->$name,
                                    $typeObject->populate($valueElement)
                                );
                            }
                        }
                    } else {
                        // a single item. Create new datatype and populate
                        $this->$name = $typeObject->populate($value);
                    }
                }
            }
        }

        return $this;
    }

    public function __call($name, $params) {
        $matches = array();
        if(preg_match('/^get(\w+)/', $name, $matches)) {
            $property = lcfirst($matches[1]);
            if(property_exists($this, $property) ) {
                return $this->$property;
            }
        }
        throw new \BadMethodCallException('Unknown method ' . $name);
    }

    /**
     * Merge current data with provided array
     * @param array $data
     */
    public function merge(array $data) {
        $this->populate((object)$data);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->encode();
    }

} 