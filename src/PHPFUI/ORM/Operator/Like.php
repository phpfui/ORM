<?php

namespace PHPFUI\ORM\Operator;

class Like extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'LIKE';
		}

	public function correctlyTyped(mixed $variable) : bool
		{
		return \is_string($variable);
		}
	}
