<?php

namespace PHPFUI\ORM;

class RelatedRecord extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters
	 */
	public function getValue(array $parameters) : mixed
		{
		$class = \array_shift($parameters);

		return new $class($this->currentRecord[$this->fieldName . \PHPFUI\ORM::$idSuffix]);
		}

	/**
	 * @param array<string> $parameters
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$class = \array_shift($parameters);

		if (! ($value instanceof $class))
			{
			throw new \PHPFUI\ORM\Exception(__METHOD__ . ': Error - ' . \get_debug_type($value) . ' is not an instance of ' . $class);
			}
		$primaryKeyValues = $value->getPrimaryKeyValues();

		if (1 != \count($primaryKeyValues))
			{
			throw new \PHPFUI\ORM\Exception(__METHOD__ . ': Error - ' . \get_debug_type($value) . ' does not have a single primary key');
			}

		$this->currentRecord[$this->fieldName . \PHPFUI\ORM::$idSuffix] = \array_shift($primaryKeyValues);
		}
	}
