<?php

namespace Tests\Fixtures\Definition;

abstract class Url extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'url' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
