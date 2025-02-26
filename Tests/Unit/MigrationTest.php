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
		$this->assertEmpty($migrator->getErrors());
		$this->assertTrue($migrator->migrationNeeded());
		$this->assertEquals(1, $migrator->migrateUpOne());
		$this->assertEquals('Migrated to 1 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$migrationTable = new \PHPFUI\ORM\Table\Migration();
		$this->assertCount(1, $migrationTable);
		$this->assertEquals(2, $migrator->migrateUpOne());
		$this->assertEquals('Migrated to 2 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertCount(2, $migrationTable);
		$this->assertEquals(1, $migrator->migrateDownOne());
		$this->assertEquals('Migrated to 1 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertCount(1, $migrationTable);
		$tables = \PHPFUI\ORM::getTables();
		$this->assertEquals(0, $migrator->migrateDownOne());
		$this->assertEquals('Migrated to 0 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertCount(0, $migrationTable);
		$this->assertEquals(3, $migrator->migrate());
		$this->assertEquals('Migrated to 3 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertCount(3, $migrationTable);
		$this->assertCount(3, $migrator);
		$this->assertEquals(3, $migrator->getCurrentMigrationId());
		$this->assertFalse($migrator->migrationNeeded());
		$migrator->migrateTo(1);
		$this->assertEquals('Migrated to 1 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertCount(1, $migrationTable);
		$this->assertCount(3, $migrator->getMigrationObjects(0, 100));
		$this->assertCount(1, $migrator->getMigrationObjects(0, 1));
		$this->assertEquals(\Tests\Fixtures\Migration\Migration_1::class, \get_debug_type($migrator->getMigrationObject(1)));

		$migrator->migrateTo(-1);
		$this->assertCount(1, $migrator->getErrors());
		$this->assertContains('Negative Migration Id (-1) is invalid', $migrator->getErrors());
		$migrator->migrateTo(4);
		$this->assertCount(2, $migrator->getErrors());
		$this->assertContains('Target migration of 4 does not exist', $migrator->getErrors());
		}
	}
