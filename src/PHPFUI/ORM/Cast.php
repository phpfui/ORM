<?php

namespace PHPFUI\ORM;

class Cast extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters
	 */
	public function getValue(array $parameters) : mixed
		{
		$class = \array_shift($parameters);

		return new $class($this->currentRecord[$this->fieldName]);
		}

	/**
	 * @param array<mixed> $parameters
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$class = \array_shift($parameters);

		if (! ($value instanceof $class))
			{
			throw new \PHPFUI\ORM\Exception(__METHOD__ . ': Error - ' . \get_debug_type($value) . ' is not an instance of ' . $class);
			}

		$this->currentRecord[$this->fieldName] = "{$value}";
		}
	}
