<?php

namespace Tests\Fixtures\Validation;

class Minvalue extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'minvalue' => ['minvalue:-10'],
	];
	}
