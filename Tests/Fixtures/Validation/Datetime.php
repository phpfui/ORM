<?php

namespace Tests\Fixtures\Validation;

class Datetime extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'datetime' => ['datetime'],
		'not_datetime' => ['!datetime'],
	];
	}
