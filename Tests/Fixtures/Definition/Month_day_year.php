<?php

namespace Tests\Fixtures\Definition;

abstract class Month_day_year extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'month_day_year' => ['sqltype', 'string', 19, false, '', false, ],
		'not_month_day_year' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
