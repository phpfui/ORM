<?php

namespace Tests\Fixtures\Definition;

abstract class Datetime extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'datetime' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
