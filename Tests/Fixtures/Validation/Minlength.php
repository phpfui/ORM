<?php

namespace Tests\Fixtures\Validation;

class Minlength extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'minlength' => ['minlength'],
		'not_minlength' => ['!minlength'],
	];
	}
