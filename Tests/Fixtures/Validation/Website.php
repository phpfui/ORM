<?php

namespace Tests\Fixtures\Validation;

class Website extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'website' => ['website'],
		'not_website' => ['!website'],
	];
	}
