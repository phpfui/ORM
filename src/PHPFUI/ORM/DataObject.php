<?php

namespace PHPFUI\ORM;

class DataObject implements \ArrayAccess
	{
	/** @param array<string, string> $current */
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

	public function isset(string $field) : bool
		{
		return \array_key_exists($field, $this->current);
		}

	public function offsetExists($offset) : bool
		{
		return \array_key_exists($offset, $this->current);
		}

	public function offsetGet($offset) : mixed
		{
		if (\array_key_exists($offset, $this->current))
			{
			return $this->current[$offset];
			}

		throw new \PHPFUI\ORM\Exception(self::class . " {$offset} is not defined");
		}

  public function offsetSet($offset, $value) : void
		{
		if (! \array_key_exists($offset, $this->current))
			{
			throw new \PHPFUI\ORM\Exception(self::class . " {$offset} is not defined");
			}
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
	}
