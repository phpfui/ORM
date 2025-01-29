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

		if ($value === null)
			{
			$this->currentRecord[$this->fieldName] = null;

			return;
			}
		else if (! ($value instanceof $class))
			{
			$value = new $class($value);
			}

		$this->currentRecord[$this->fieldName] = "{$value}";
		}
	}
