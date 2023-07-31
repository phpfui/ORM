<?php

namespace Tests\Fixtures\Validation;

class Enum extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'enum' => ['enum:GET,POST,PUT,DELETE'],
		'not_enum' => ['!enum:GET,POST,PUT,DELETE'],
	];
	}
