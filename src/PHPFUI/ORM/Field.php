<?php

namespace PHPFUI\ORM;

/**
 * A class representing a SQL field name.  Used to pass into a \PHPFUI\ORM\Condition to distigush values and fields.
 */
class Field implements \Stringable
	{
	private string $fieldName = '';

	public function __construct(string $name, string $as = '')
		{
		$parts = \explode('.', $name);
		$dot = '';

		foreach ($parts as $part)
			{
			$this->fieldName .= $dot . '`' . $part . '`';
			$dot = '.';
			}

		if ($as)
			{
			$this->fieldName .= ' AS `' . $as . '`';
			}
		}

	public function __toString() : string
		{
		return $this->fieldName;
		}
	}
