<?php

namespace Tests\Fixtures\Validation;

class Month_day_year extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'month_day_year' => ['month_day_year'],
		'not_month_day_year' => ['!month_day_year'],
	];
	}
