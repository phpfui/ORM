<?php

namespace Tests\Fixtures\Definition;

abstract class DateISO extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'dateISO' => ['sqltype', 'string', 19, false, '', false, ],
		'not_dateISO' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
