<?php

namespace AuctioCore\Api\Auctio\Entity\Abs;

use AuctioCore\Api\Auctio\Defaults;

abstract class BaseList implements \AuctioCore\Api\Auctio\Entity\Interfaces\Base, \ArrayAccess, \Iterator, \Countable
{

	/**
	 * @var array
	 */
	public $_items = array();
	protected $_position = 0;

	public function __construct($data = array()) {
		if(!empty($data)) {
			$this->populate($data);
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
				if(strpos($property->getDocComment(), '@ReadOnly') === false) {
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
			if(is_object($var) && !($var instanceof stdClass) ) {
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
	 * @return string
	 */
	function __toString() {
		return $this->encode();
	}


	protected function getItemType()
	{
		static $type;
		if (!$type) {
			$class = new \ReflectionClass($this);

			if (!preg_match('/@return\s+([a-zA-Z\\\\]+)/s', $class->getDocComment(), $matches)) {
				throw new \Exception('_item have no Type configured');
			}
			$type = $matches[1];
		}

		return $type;
	}

	/**
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return isset($this->_items[$offset]);
	}

	/**
	 * The offset to retrieve.
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->_items[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$ref = new \ReflectionClass(get_called_class());
		$offset = $offset ? : count($this->_items);
		$className = $ref->getNamespaceName() . '\\' . $this->getItemType();

		$item = new $className;
		$this->_items[$offset] = $item->populate($value);
	}

	/**
	 * @param mixed $offset <p>
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->_items[$offset]);
	}

	/**
	 * @param array $data
	 */
	public function populate($data)
	{
		foreach ($data as $dataElement) {
			$this[] = $dataElement;
		}
	}

	/**
	 * @return mixed
	 */
	public function current() {
		return $this->_items[$this->_position];
	}

	/**
	 * @return int
	 */
	public function key() {
		return $this->_position;
	}

	public function valid() {
		return isset($this->_items[$this->_position]);
	}

	public function next() {
		++$this->_position;
	}

	public function rewind() {
		$this->_position = 0;
	}

	/**
	 * @return int
	 */
	public function count() {
		return count($this->_items);
	}

	/**
	 * Gets a slice of elements
	 * @param int $offset
	 * @param int $number
	 * @return array
	 */
	public function slice($offset, $number = null) {
		$number = $number ?: $this->count();
		$elements = array();

		for($i = $offset; $i < $number; $i++) {
			$elements[$i] = $this->offsetGet($i);
		}

		return $elements;
	}

	/**
	 * @param callable $filter
	 * @return array|static
	 */
	public function filter(callable $filter, $returnSelf = false) {
		$elements = array_filter($this->_items, $filter);

		return $returnSelf ? new static($elements) : $elements;
	}
} 