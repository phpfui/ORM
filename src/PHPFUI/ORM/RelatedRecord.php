<?php

namespace PHPFUI\ORM;

/**
 * get a related record
 */
class RelatedRecord extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters recordClassName fieldName
	 */
	public function getValue(array $parameters) : mixed
		{
		$class = \array_shift($parameters);
		$field = \array_shift($parameters);

		if ($field)
			{
			return new $class($this->currentRecord->{$field});
			}

		return new $class($this->currentRecord[$this->fieldName . \PHPFUI\ORM::$idSuffix]);
		}

	/**
	 * @param array<string> $parameters
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$class = \array_shift($parameters);
		$field = \array_shift($parameters);

		if (\get_debug_type($value) != $class)
			{
			throw new \PHPFUI\ORM\Exception(__METHOD__ . ': Error - ' . \get_debug_type($value) . ' is not an instance of ' . $class);
			}

		$primaryKeyValues = $value->getPrimaryKeyValues();

		if (1 != \count($primaryKeyValues))
			{
			throw new \PHPFUI\ORM\Exception(__METHOD__ . ': Error - ' . \get_debug_type($value) . ' does not have a single primary key');
			}

		if (! $field)
			{
			$field = $this->fieldName . \PHPFUI\ORM::$idSuffix;
			}

		$this->currentRecord[$field] = \array_shift($primaryKeyValues);
		}
	}
