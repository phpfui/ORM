<?php

namespace Tests\Fixtures\Validation;

class Required extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'required' => ['required'],
		'not_required' => ['!required'],
	];
	}
