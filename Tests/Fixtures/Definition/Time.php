<?php

namespace Tests\Fixtures\Definition;

abstract class Time extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'time' => ['sqltype', 'string', 19, false, '', false, ],
		'not_time' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
