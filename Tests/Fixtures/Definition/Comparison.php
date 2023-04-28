<?php

namespace Tests\Fixtures\Definition;

abstract class Comparison extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'equal' => ['sqltype', 'string', 50, false, '', false, ],
		'not_equal' => ['sqltype', 'string', 50, false, '', false, ],
		'gt_field' => ['sqltype', 'string', 50, false, '', false, ],
		'gte_field' => ['sqltype', 'string', 50, false, '', false, ],
		'lt_field' => ['sqltype', 'string', 50, false, '', false, ],
		'lte_field' => ['sqltype', 'string', 50, false, '', false, ],
		'eq_field' => ['sqltype', 'string', 50, false, '', false, ],
		'neq_field' => ['sqltype', 'string', 50, false, '', false, ],
		'date' => ['date', 'string', 50, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
