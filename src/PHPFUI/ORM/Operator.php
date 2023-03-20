<?php

namespace PHPFUI\ORM;

/**
 * Operator is an abstract class to specify valid SQL operators for \PHPFUI\ORM\Condition
 */
abstract class Operator implements \Stringable
	{
	protected string $operator = '';

	public function __toString() : string
		{
		return $this->operator;
		}

	/**
	 * Return true if the variable passed is of the correct type for the operator.  Normally a scalar, but LIKE needs a string and IN needs an array.
	 */
	public function correctlyTyped($variable) : bool
		{
		return \is_scalar($variable);
		}

	public function getOperatorString() : string
		{
		return $this->operator;
		}
	}
