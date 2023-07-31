<?php

namespace Tests\Fixtures\Definition;

abstract class Card extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'card' => ['sqltype', 'string', 19, false, '', false, ],
		'not_card' => ['sqltype', 'string', 19, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
