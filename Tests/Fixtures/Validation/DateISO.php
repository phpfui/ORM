<?php

namespace Tests\Fixtures\Validation;

class DateISO extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'dateISO' => ['dateISO'],
		'not_dateISO' => ['!dateISO'],
	];
	}
