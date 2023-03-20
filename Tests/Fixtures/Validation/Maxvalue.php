<?php

namespace Tests\Fixtures\Validation;

class Maxvalue extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'maxvalue' => ['maxvalue:10'],
		'value' => ['maxvalue:15', 'minvalue:5'],
	];
	}
