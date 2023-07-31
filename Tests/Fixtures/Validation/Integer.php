<?php

namespace Tests\Fixtures\Validation;

class Integer extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'integer' => ['integer'],
		'not_integer' => ['!integer'],
	];
	}
