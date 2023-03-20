<?php

namespace PHPFUI\ORM;

/**
 * A Cursor does not read the entire query result into memory at once, but will just read and return one row at a time.
 *
 * Since it is iterable, it can be used in a foreach statement.  The index or key will be an integer starting at 0 for the first record returned.
 */
abstract class BaseCursor implements \Countable, \Iterator
	{
	protected int $index = -1;

	private ?int $count = null;

	private ?int $total = null;

	private ?\PDOStatement $countStatement = null;

	private ?\PDOStatement $totalStatement = null;

	/** @param array<mixed> $input */
	public function __construct(protected ?\PDOStatement $statement = null, protected readonly array $input = [])
		{
		}

	public function __destruct()
		{
		$this->statement?->closeCursor();
		$this->index = -1;
		$this->total = null;
		$this->count = null;
		}

	public function setQueryCount(int $count) : self
		{
		$this->count = $count;

		return $this;
		}

	/**
	 * **count** is the actual number of records returned by the query, which should less than or equal to the limit clause if it was used.
	 *
	 * See **total** to get the number of records in the table without a limit clause.
	 */
	public function count() : int
		{
		if (null === $this->count && $this->countStatement)
			{
			if ($this->countStatement->execute($this->input))
				{
				$this->count = (int)$this->countStatement->fetch(\PDO::FETCH_NUM)[0];
				}
			}

		if (null === $this->count)
			{
			$this->init();
			$this->count = $this->statement ? $this->statement->rowCount() : 0;
			}

		return $this->count;
		}

	/**
	 * @return mixed  representation of the current row
	 */
	abstract public function current() : mixed;

	/**
	 * The offset of the row in the query, 0 based
	 */
	public function key() : int
		{
		$this->init();

		return $this->index;
		}

	/**
	 * Go to the next record
	 */
	abstract public function next() : void;

	/**
	 * Reset the cursor to the beginning of the set
	 */
	public function rewind() : void
		{
		$this->index = 0;

		if (! $this->statement)
			{
			return;
			}

		$this->statement->closeCursor();

		$result = \PHPFUI\ORM::executeStatement($this->statement, $this->input);

		if ($result)
			{
			$this->next();
			}
		}

	/**
	 * Sets the count when a limit clause is used.
	 */
	public function setCountSQL(string $limitedSql) : static
		{
		$this->countStatement = \PHPFUI\ORM::pdo()->prepare($limitedSql);

		return $this;
		}

	/**
	 * Sets the count for the full query with no limit clause
	 */
	public function setTotalCountSQL(string $totalSql) : static
		{
		$this->totalStatement = \PHPFUI\ORM::pdo()->prepare($totalSql);

		return $this;
		}

	/**
	 * Returns the total number records returned in the query without a limit clause
	 *
	 * See **count** if you need the number of records the query returned.
	 */
	public function total() : int
		{
		if (null === $this->total && $this->totalStatement)
			{
			if ($this->totalStatement->execute($this->input))
				{
				$this->total = (int)$this->totalStatement->fetch(\PDO::FETCH_NUM)[0];
				}
			}

		if (null === $this->total)
			{
			$this->total = $this->count();
			}

		return $this->total;
		}

	/**
	 * Returns true if not at the end of the input
	 */
	abstract public function valid() : bool;

	/**
	 * Internal method to make sure rewind is called before anything else but will save the query if never executed.
	 */
	protected function init() : void
		{
		if (-1 == $this->index)
			{
			$this->rewind();
			}
		}
	}
