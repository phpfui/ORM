<?php

namespace PHPFUI\ORM;

class Enum extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 **/
	public function fromPHPValue(mixed $value, array $parameters) : mixed
		{
		$enum = $parameters[0];

		return $enum::from($value ?? 0);
		}

	/**
	 * @param array<mixed> $parameters optional
	 */
	public function getValue(array $parameters) : mixed
		{
		$enum = $parameters[0];

		return $enum::from($this->currentRecord->offsetGet($this->fieldName) ?? 0);
		}

	/**
	 * @param array<mixed> $parameters optional
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$enum = $parameters[0];

		if ($value instanceof $enum)
			{
			$this->currentRecord->offsetSet($this->fieldName, $value->value);

			return;
			}

		throw new \PHPFUI\ORM\Exception('You can not assign a variable of type ' . \get_debug_type($value) . ' to an enum type of ' . $enum);
		}
	}
