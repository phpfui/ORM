<?php

namespace PHPFUI\ORM;

/**
 * A Cursor does not read the entire query result into memory at once, but will just read and return one row at a time.
 *
 * Since it is iterable, it can be used in a foreach statement.  The index or key will be an integer starting at 0 for the first record returned.
 */
class ArrayCursor extends \PHPFUI\ORM\BaseCursor
	{
	/** @var array<string,?string> */
	private array $current = [];

	/**
	 * @return array<string, string> representation of the current row
	 */
	public function current() : array
		{
		$this->init();

		return $this->current;
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
