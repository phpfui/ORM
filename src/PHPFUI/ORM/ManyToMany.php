<?php

namespace PHPFUI\ORM;

/**
 * Many To Many relationships need a junction table containing the primary keys of the two tables needing the many to many relationshp.
 *
 * In the **Record** class definition, you need to define a virtual field with the name of the Many To Many relationship.  The key of the virtual field is the member name and the value is an array.
 *
 * The values in the array should be **\PHPFUI\ORM\ManyToMany::class**, followed by the junction table class name, then related table class name.
 * Two additional parameters can be specified, the order by column and sort order (defaults to ASC).
 *
 * Example:
 * ```php
 * protected static array $virtualFields = [
 *   'suppliers' => [\PHPFUI\ORM\ManyToMany::class, \Tests\App\Table\ProductSupplier::class, \Tests\App\Table\Supplier::class, 'company', ],
 * ];
 * ```
 */
class ManyToMany extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string, string[]> $parameters Key is the field name, values should be **\PHPFUI\ORM\ManyToMany::class**, followed by the junction table class name, then related table class name. Two additional parameters can be specified, the order by column and sort order (defaults to ASC).
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$junctionTableClass = \array_shift($parameters);
		$junctionTable = new $junctionTableClass();
		$relatedTableClass = \array_shift($parameters);
		$relatedTable = new $relatedTableClass();
		$junctionTableName = $junctionTable->getTableName();
		$relatedTableName = $relatedTable->getTableName();
		$relatedTable->addJoin($junctionTableName, $relatedTableName . \PHPFUI\ORM::$idSuffix);
		$condition = new \PHPFUI\ORM\Condition();

		foreach ($this->currentRecord->getPrimaryKeys() as $primaryKey)
			{
			$condition->and($junctionTableName . '.' . $primaryKey, $this->currentRecord->{$primaryKey});
			}

		foreach ($relatedTable->getPrimaryKeys() as $primaryKey)
			{
			$condition->and(new \PHPFUI\ORM\Field($junctionTableName . '.' . $primaryKey), new \PHPFUI\ORM\Field($relatedTableName . '.' . $primaryKey));
			}

		$relatedTable->setWhere($condition);

		$orderBy = \array_shift($parameters);
		$sort = \array_shift($parameters) ?? 'asc';

		if ($orderBy)
			{
			$relatedTable->addOrderBy($orderBy, $sort);
			}

		return $relatedTable->getRecordCursor();
		}
	}
