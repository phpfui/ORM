<?php

namespace PHPFUI\ORM\Operator;

class IsNull extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'IS NULL';
		}

	public function correctlyTyped($variable) : bool
		{
		return null === $variable;
		}
	}
