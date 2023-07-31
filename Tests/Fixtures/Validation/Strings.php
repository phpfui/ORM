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
		'not_starts_with' => ['starts_with:a,b,c'],
		'not_ends_with' => ['!ends_with:a,b,c'],
		'not_contains' => ['!contains:a,b,c'],
		'not_istarts_with' => ['!istarts_with:a,b,c'],
		'not_iends_with' => ['!iends_with:a,b,c'],
		'not_icontains' => ['!icontains:a,b,c'],
	];
	}
