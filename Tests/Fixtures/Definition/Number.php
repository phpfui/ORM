<?php

namespace Tests\Fixtures\Definition;

abstract class Number extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'number' => ['sqltype', 'string', 19, false, '', false, ],
		'not_number' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
