<?php

namespace Tests\Fixtures\Definition;

abstract class Maxlength extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'maxlength' => ['sqltype', 'string', 19, false, '', false, ],
		'not_maxlength' => ['!sqltype', 'string', 19, false, '', false, ],
		'length' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
