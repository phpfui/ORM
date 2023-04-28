<?php

namespace Tests\Fixtures\Validation;

class Strings extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'starts_with' => ['starts_with:a,b,c'],
		'ends_with' => ['ends_with:a,b,c'],
		'contains' => ['contains:a,b,c'],
		'istarts_with' => ['istarts_with:a,b,c'],
		'iends_with' => ['iends_with:a,b,c'],
		'icontains' => ['icontains:a,b,c'],
	];
	}
