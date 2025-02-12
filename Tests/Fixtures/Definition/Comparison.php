<?php

namespace Tests\Fixtures\Definition;

abstract class Comparison extends \PHPFUI\ORM\Record
{
	public static bool $autoIncrement = false;

	public static array $fields = [];

	public static string $primaryKey = '';

	public static string $table = '';

	public function initFieldDefinitions() : static
		{
		if (! \count(static::$fields))
			{
			static::$fields = [
				'equal' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_equal' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'gt_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'gte_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'lt_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'lte_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'eq_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'neq_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'date' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 50, false, '', false, ),
				'not_equal' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_not_equal' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_gt_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_gte_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_lt_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_lte_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_eq_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_neq_field' => new \PHPFUI\ORM\FieldDefinition('sqltype', 'string', 50, false, '', false, ),
				'not_date' => new \PHPFUI\ORM\FieldDefinition('date', 'string', 50, false, '', false, ),
			];
			}

		return $this;
		}
}
