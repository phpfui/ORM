<?php

namespace Tests\Fixtures\Definition;

abstract class Strings extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [
		'starts_with' => ['sqltype', 'string', 50, false, '', false, ],
		'ends_with' => ['sqltype', 'string', 50, false, '', false, ],
		'contains' => ['sqltype', 'string', 50, false, '', false, ],
		'istarts_with' => ['sqltype', 'string', 50, false, '', false, ],
		'iends_with' => ['sqltype', 'string', 50, false, '', false, ],
		'icontains' => ['sqltype', 'string', 50, false, '', false, ],
		'not_starts_with' => ['sqltype', 'string', 50, false, '', false, ],
		'not_ends_with' => ['sqltype', 'string', 50, false, '', false, ],
		'not_contains' => ['sqltype', 'string', 50, false, '', false, ],
		'not_istarts_with' => ['sqltype', 'string', 50, false, '', false, ],
		'not_iends_with' => ['sqltype', 'string', 50, false, '', false, ],
		'not_icontains' => ['sqltype', 'string', 50, false, '', false, ],
	];

	public static string $primaryKey = '';

	public static string $table = '';
}
