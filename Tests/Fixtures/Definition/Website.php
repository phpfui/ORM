<?php

namespace Tests\Fixtures\Definition;

abstract class Website extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'website' => ['sqltype', 'string', 19, false, '', false, ],
		'not_website' => ['!sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
