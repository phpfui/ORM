<?php

namespace Tests\Fixtures\Validation;

class Unique extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'unique' => ['unique'],
	];
	}
