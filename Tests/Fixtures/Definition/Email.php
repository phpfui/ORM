<?php

namespace Tests\Fixtures\Definition;

abstract class Email extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'email' => ['sqltype', 'string', 19, false, '', false, ],
		'not_email' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
