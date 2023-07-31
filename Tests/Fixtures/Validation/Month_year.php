<?php

namespace Tests\Fixtures\Validation;

class Month_year extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'month_year' => ['month_year'],
		'not_month_year' => ['!month_year'],
	];
	}
