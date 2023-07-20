<?php

namespace PHPFUI\ORM\Operator;

class IsNotNull extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'IS NOT NULL';
		}

	public function correctlyTyped(mixed $variable) : bool
		{
		return null === $variable;
		}
	}
