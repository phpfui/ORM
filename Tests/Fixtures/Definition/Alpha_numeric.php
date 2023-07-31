<?php

namespace Tests\Fixtures\Definition;

abstract class Alpha_numeric extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'alpha_numeric' => ['sqltype', 'string', 19, false, '', false, ],
		'not_alpha_numeric' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
