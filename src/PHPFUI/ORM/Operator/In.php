<?php

namespace PHPFUI\ORM\Operator;

class In extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'IN';
		}

	public function correctlyTyped(mixed $variable) : bool
		{
		return (\is_array($variable) && count($variable)) || $variable instanceof \PHPFUI\ORM\Table;
		}
	}
