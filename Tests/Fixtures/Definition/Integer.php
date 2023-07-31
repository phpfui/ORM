<?php

namespace Tests\Fixtures\Definition;

abstract class Integer extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'integer' => ['sqltype', 'int', 19, false, '', false, ],
		'not_integer' => ['!sqltype', 'int', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
