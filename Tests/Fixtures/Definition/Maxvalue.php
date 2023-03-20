<?php

namespace Tests\Fixtures\Definition;

abstract class Maxvalue extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'maxvalue' => ['sqltype', 'string', 19, false, '', false, ],
		'value' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
