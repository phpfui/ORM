<?php

namespace Tests\Fixtures\Validation;

class Email extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'email' => ['email'],
	];
	}
