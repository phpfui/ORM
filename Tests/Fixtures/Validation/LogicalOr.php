<?php

namespace Tests\Fixtures\Validation;

class LogicalOr extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'alpha' => ['alpha|starts_with:/', 'required', 'ends_with:4'],
	];
	}
