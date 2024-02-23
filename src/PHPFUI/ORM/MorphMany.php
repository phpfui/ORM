<?php

namespace PHPFUI\ORM;

/**
 * A MorphMany class to mimic Eloquent MorphMany.
 *
 * Requirements:
 *
 * The morphing records needs two fields: an integer related key and a string field type long enough to hold the table name of the relationship.  They should both be prefixed with the morph field prefix passed into the class. The related key field should follow the same id naming conventions as the rest of the database.
 *
 * In the **Record** class definition, you need to define a virtual field with the name of the MorphMany relationship.  The key of the virtual field is the member name and the value is an array.
 *
 * The values in the array should be **\PHPFUI\ORM\MorphMany::class**, followed by the morphing table class name, then morph field prefix.  Sort field and sort order are optional fields.
 *
 * Example:
 * ```php
 * protected static array $virtualFields = [
 *   'images' => [\PHPFUI\ORM\MorphToMany::class, \Tests\App\Table\Image::class, 'imageable', ],
 * ];
 * ```
 */
class MorphMany extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters morphing table class name, morph field prefix, optional sort column, optional sort order.
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$morphTableClass = \array_shift($parameters);
		$morphTable = new $morphTableClass();
		$morphFieldPrefix = \array_shift($parameters);
		$condition = new \PHPFUI\ORM\Condition($morphFieldPrefix . '_type', $this->currentRecord->getTableName());
		$primaryKey = $this->currentRecord->getPrimaryKeys()[0];
		$condition->and($morphFieldPrefix . \PHPFUI\ORM::$idSuffix, $this->currentRecord->{$primaryKey});
		$morphTable->setWhere($condition);

		$orderBy = \array_shift($parameters);
		$sort = \array_shift($parameters) ?? 'asc';

		if ($orderBy)
			{
			$morphTable->addOrderBy($orderBy, $sort);
			}

		return $morphTable->getRecordCursor();
		}

	/**
	 * @param mixed $value to add as morph relation for the current record
	 * @param array<string> $parameters morphing table class name, morph field prefix, optional sort column, optional sort order
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$morphTableClass = \array_shift($parameters);
		$morphTable = new $morphTableClass();
		$morphFieldPrefix = \array_shift($parameters);
		$primaryKey = $this->currentRecord->getPrimaryKeys()[0];

		$morphTypeField = $morphFieldPrefix . '_type';
		$morphIdField = $morphFieldPrefix . \PHPFUI\ORM::$idSuffix;

		$value->{$morphTypeField} = $this->currentRecord->getTableName();
		$primaryKeyValue = $this->currentRecord->{$primaryKey};

		if (! $primaryKeyValue)
			{
			$this->currentRecord->insert();
			$primaryKeyValue = $this->currentRecord->{$primaryKey};
			}
		$value->{$morphIdField} = $primaryKeyValue;

		$value->insert();
		}
	}
