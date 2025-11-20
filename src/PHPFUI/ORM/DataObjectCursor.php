<?php

namespace PHPFUI\ORM;

/**
 * A Cursor does not read the entire query result into memory at once, but will just read and return one row at a time.
 *
 * Since it is iterable, it can be used in a foreach statement.
 *
 * The DataObjectCursor returns an object you access via object syntax (ie. $object->field), vs array
 * syntax ($array['field']) for the ArrayCursor
 */
class DataObjectCursor extends \PHPFUI\ORM\BaseCursor
	{
	/** @var array<string,string> */
	private array $current;

	/**
	 * @return mixed representation of the current row
	 */
	public function current() : mixed
		{
		$this->init();

		return new \PHPFUI\ORM\DataObject($this->current ?: []);
		}

	/**
	 * Go to the next record
	 */
	public function next() : void
		{
		$this->init();
		$data = $this->statement ? $this->statement->fetch(\PDO::FETCH_ASSOC) : [];
		$this->current = $data ? \PHPFUI\ORM::expandResources($data) : [];
		++$this->index;
		}

	/**
	 * Returns true if not at the end of the input
	 */
	public function valid() : bool
		{
		$this->init();

		return ! empty($this->current);
		}
	}
