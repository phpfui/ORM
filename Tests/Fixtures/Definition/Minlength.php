<?php

namespace Tests\Fixtures\Definition;

abstract class Minlength extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'minlength' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
