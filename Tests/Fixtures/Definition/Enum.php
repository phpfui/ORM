<?php

namespace Tests\Fixtures\Definition;

abstract class Enum extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'enum' => ['sqltype', 'string', 19, false, '', false, ],
		'not_enum' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
