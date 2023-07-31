<?php

namespace Tests\Fixtures\Validation;

class Maxlength extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'maxlength' => ['maxlength'],
		'not_maxlength' => ['!maxlength'],
		'length' => ['maxlength:20', 'minlength:2'],
	];
	}
