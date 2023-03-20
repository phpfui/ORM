<?php

namespace PHPFUI\ORM\Operator;

class In extends \PHPFUI\ORM\Operator
	{
	public function __construct()
		{
		$this->operator = 'IN';
		}

	public function correctlyTyped($variable) : bool
		{
		return \is_array($variable) || $variable instanceof \PHPFUI\ORM\Table;
		}
	}
