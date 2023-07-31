<?php

namespace Tests\Fixtures\Validation;

class Domain extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'domain' => ['domain'],
		'not_domain' => ['!domain'],
	];
	}
