<?php

namespace Tests\Fixtures\Definition;

abstract class Cvv extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'cvv' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
