<?php

namespace PHPFUI\ORM\Operator;

class NotIn extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'NOT IN';
		}

	public function correctlyTyped(mixed $variable) : bool
		{
		return (\is_array($variable) && \count($variable)) || $variable instanceof \PHPFUI\ORM\Table;
		}
	}
