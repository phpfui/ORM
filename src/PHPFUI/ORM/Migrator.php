<?php

namespace PHPFUI\ORM;

class Migrator implements \Countable
	{
	/** @var array<string|array<string,string>> */
	private array $errors = [];

	private readonly \PHPFUI\ORM\Table\Migration $migrationTable;

	private string $status = '';

	public function __construct()
		{
		$this->migrationTable = new \PHPFUI\ORM\Table\Migration();
		}

	/**
	 * @return int number of migrations in the system
	 */
	public function count() : int
		{
		$fi = new \FilesystemIterator(\PHPFUI\ORM::getMigrationNamespacePath(), \FilesystemIterator::SKIP_DOTS);
		$count = \iterator_count($fi);

		return $count;
		}

	/**
	 * @return int the highest migration id stored in the migration table
	 */
	public function getCurrentMigrationId() : int
		{
		try
			{
			$highest = $this->migrationTable->getHighest();

			return $highest->migrationId;
			}
		catch (\Throwable $e)
			{
			}

		return 0;
		}

	/**
	 * @return array<string|array<string,string>>
	 */
	public function getErrors() : array
		{
		return $this->errors;
		}

	/**
	 * Get a specific migration class instance
	 */
	public function getMigrationObject(int $migrationId) : ?\PHPFUI\ORM\Migration
		{
		$className = \PHPFUI\ORM::$migrationNamespace . "\\Migration_{$migrationId}";

		if (\class_exists($className))
			{
			$object = new $className();

			try
				{
				$migration = new \PHPFUI\ORM\Record\Migration($migrationId);

				if (! $migration->empty())
					{
					$object->setRan($migration->ran);
					}
				}
			catch (\Throwable)
				{
				}

			return $object;
			}

		return null;
		}

	/**
	 * @return \PHPFUI\ORM\Migration[]
	 */
	public function getMigrationObjects(int $page, int $perPage) : array
		{
		$objects = [];

		$total = $this->count();

		$start = $total - ($page + 1) * $perPage + 1;
		$end = $start + $perPage;

		for ($i = $end; --$i >= $start;)
			{
			$object = $this->getMigrationObject($i);

			if ($object)
				{
				$objects[$object->id()] = $object;
				}
			}

		return $objects;
		}

	/**
	 * @return string user friendly status message for display
	 */
	public function getStatus() : string
		{
		return $this->status;
		}

	/**
	 * Migrates to the latest
	 *
	 * @return bool true if migration happened
	 */
	public function migrate() : bool
		{
		$current = $this->getCurrentMigrationId();

		while ($this->migrateUpOne())
			{
			}
		$final = $this->getCurrentMigrationId();

		return $final != $current;
		}

	/**
	 * Migrate one down
	 *
	 * @return int new migration level or zero if error
	 */
	public function migrateDownOne() : int
		{
		$highest = $this->migrationTable->getHighest();

		if ($highest->empty())
			{
			$this->status = 'No previous migration';

			return 0;
			}

		$id = (int)$highest->migrationId;

		$migration = $this->getMigrationObject($id);

		if (! $migration)
			{
			$this->errors[] = "Previous migration not found ({$id})";

			return 0;
			}
		$result = $this->runDown($migration);

		return $result;
		}

	/**
	 * Migrate (up or down) to a specific migrationId
	 *
	 * @return bool true if migration happened
	 */
	public function migrateTo(int $migrationId) : bool
		{
		if ($migrationId < 0)
			{
			$this->errors[] = "Negative Migration Id ({$migrationId}) is invalid";

			return false;
			}

		if ($migrationId && ! $this->getMigrationObject($migrationId))
			{
			$this->errors[] = "Target migration of {$migrationId} does not exist";

			return false;
			}

		$currentMigrationId = $this->getCurrentMigrationId();

		while ($currentMigrationId < $migrationId)
			{
			$nextMigrationId = $this->migrateUpOne();

			if (! $nextMigrationId)
				{
				++$currentMigrationId;
				$this->errors[] = "Migrated to {$currentMigrationId} failed";

				return false;
				}
			$currentMigrationId = $nextMigrationId;
			}

		while ($currentMigrationId > $migrationId)
			{
			$previous = $this->migrateDownOne();

			if (! $previous && $currentMigrationId > 1)
				{
				--$currentMigrationId;
				$this->errors[] = "Migrated to {$migrationId} failed";

				return false;
				}
			$currentMigrationId = $previous;
			}

		if ($currentMigrationId === $migrationId)
			{
			$this->status = "Migrated to {$migrationId} successfully";

			return true;
			}

		return false;
		}

	/**
	 * Migrate one up
	 *
	 * @return int new migration level or zero if error
	 */
	public function migrateUpOne() : int
		{
		$id = $this->getCurrentMigrationId();
		$migration = $this->getMigrationObject(++$id);

		if (! $migration)
			{
			return 0;
			}
		$result = $this->runUp($migration);

		return $result;
		}

	/**
	 * @return bool true if a migration needs to be run
	 */
	public function migrationNeeded() : bool
		{
		return $this->count() > $this->getCurrentMigrationId();
		}

	/**
	 * @return int current migration or zero if error
	 */
	private function runDown(\PHPFUI\ORM\Migration $migration) : int
		{
		if (! $migration->down() || ! $migration->executeAlters() || $migration->getErrors())
			{
			$this->errors = \array_merge($this->errors, $migration->getErrors());

			return 0;
			}
		$migrationRecord = new \PHPFUI\ORM\Record\Migration($migration->id());
		$migrationRecord->delete();

		$highest = $this->migrationTable->getHighest();

		$id = (int)$highest->migrationId;
		$this->status = "Migrated to {$id} successfully";

		return $id;
		}

	/**
	 * @return int current migration or zero if error
	 */
	private function runUp(\PHPFUI\ORM\Migration $migration) : int
		{
		if (! $migration->up() || ! $migration->executeAlters() || $migration->getErrors())
			{
			$this->errors = \array_merge($this->errors, $migration->getErrors());

			return 0;
			}
		$migrationRecord = new \PHPFUI\ORM\Record\Migration();
		$migrationRecord->migrationId = $migration->id();
		$migrationRecord->insert();

		$id = $migrationRecord->migrationId;
		$this->status = "Migrated to {$id} successfully";

		return $id;
		}
	}
