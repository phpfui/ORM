<?php

namespace Tests\Unit;

class MigrationTest extends \PHPUnit\Framework\TestCase
	{
	public function testMigrations() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$tables = \PHPFUI\ORM::getTables();
		$this->assertContains('migration', $tables);
		$migrator = new \PHPFUI\ORM\Migrator();
		$migrator->migrateUpOne();
		$migrationTable = new \PHPFUI\ORM\Table\Migration();
		$this->assertCount(1, $migrationTable);
		$migrator->migrateUpOne();
		$this->assertCount(2, $migrationTable);
		$migrator->migrateDownOne();
		$this->assertCount(1, $migrationTable);
		$tables = \PHPFUI\ORM::getTables();
		$migrator->migrateDownOne();
		$this->assertCount(0, $migrationTable);
		$migrator->migrate();
		$this->assertCount(3, $migrationTable);
		}
	}
