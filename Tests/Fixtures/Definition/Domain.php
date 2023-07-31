<?php

namespace Tests\Fixtures\Definition;

abstract class Domain extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'domain' => ['sqltype', 'string', 19, false, '', false, ],
		'not_domain' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
