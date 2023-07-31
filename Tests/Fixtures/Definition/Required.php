<?php

namespace Tests\Fixtures\Definition;

abstract class Required extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'required' => ['sqltype', 'string', 19, false, '', false, ],
		'not_required' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
