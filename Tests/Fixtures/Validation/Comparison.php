<?php

namespace Tests\Fixtures\Validation;

class Comparison extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'equal' => ['equal:2023-01-01'],
		'not_equal' => ['not_equal:2023-01-01'],
		'gt_field' => ['gt_field:date'],
		'gte_field' => ['gte_field:date'],
		'lt_field' => ['lt_field:date'],
		'lte_field' => ['lte_field:date'],
		'eq_field' => ['eq_field:date'],
		'neq_field' => ['neq_field:date'],
		'date' => ['date'],
	];
	}
