<?php

namespace PHPFUI\ORM;

/**
 * @implements \ArrayAccess<string,string>
 */
class DataObject implements \ArrayAccess
	{
	/** @param array<string, string | null | int | bool | float> $current */
	public function __construct(protected array $current)
		{
		}

	public function __get(string $field) : mixed
		{
		if (\array_key_exists($field, $this->current))
			{
			return $this->current[$field];
			}

		// could be a related record, see if has a matching Id
		if (\array_key_exists($field . \PHPFUI\ORM::$idSuffix, $this->current))
			{
			$type = '\\' . \PHPFUI\ORM::$recordNamespace . '\\' . \PHPFUI\ORM::getBaseClassName($field);

			if (\class_exists($type))
				{
				return new $type($this->current[$field . \PHPFUI\ORM::$idSuffix]);
				}
			}

		throw new \PHPFUI\ORM\Exception(self::class . " {$field} is not a valid field");
		}

	/**
	 * Allows for empty($object->field) to work correctly
	 */
  public function __isset(string $field) : bool
		{
		return \array_key_exists($field, $this->current) || ! empty($this->current[$field . \PHPFUI\ORM::$idSuffix]);
		}

	public function __set(string $field, mixed $value) : void
		{
		if (! \array_key_exists($field, $this->current))
			{
			throw new \PHPFUI\ORM\Exception(self::class . " {$field} is not defined");
			}

		$this->current[$field] = $value;
		}

	public function empty() : bool
		{
		return ! \count($this->current);
		}

	public function initFieldDefinitions() : static
		{
		return $this;
		}

	public function isset(string $field) : bool
		{
		return $this->__isset($field);
		}

	public function offsetExists($offset) : bool
		{
		return \array_key_exists($offset, $this->current);
		}

	/**
	 * Low level get access to underlying data to implement ArrayAccess
	 */
	public function offsetGet($offset) : mixed
		{
		$this->validateFieldExists($offset);

		return $this->current[$offset] ?? null;
		}

	/**
	 * Low level set access to underlying data to implement ArrayAccess
	 */
	public function offsetSet($offset, $value) : void
		{
		$this->validateFieldExists($offset);
		$this->current[$offset] = $value;
		}

	public function offsetUnset($offset) : void
		{
		unset($this->current[$offset]);
		}

	/** @return array<string, mixed> */
	public function toArray() : array
		{
		return $this->current;
		}

	protected function validateFieldExists(string $field) : void
		{
		if (! $this->__isset($field))
			{
			$message = static::class . "::{$field} is not a valid field";
			\PHPFUI\ORM::log(\Psr\Log\LogLevel::ERROR, $message);

			throw new \PHPFUI\ORM\Exception($message);
			}
		}
	}
