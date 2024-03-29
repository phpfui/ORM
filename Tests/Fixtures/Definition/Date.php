<?php

namespace Tests\Fixtures\Definition;

abstract class Date extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'date' => ['sqltype', 'string', 19, false, '', false, ],
		'not_date' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
