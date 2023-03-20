<?php

namespace Tests\Fixtures\Definition;

abstract class Year_month extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'year_month' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
