<?php

namespace PHPFUI\ORM\Operator;

class NotLike extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'NOT LIKE';
		}

	public function correctlyTyped($variable) : bool
		{
		return \is_string($variable);
		}
	}
