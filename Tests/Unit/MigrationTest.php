<?php

namespace Tests\Unit;

class MigrationTest extends \PHPUnit\Framework\TestCase
	{
	public function testAddDropColumn() : void
		{
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));

		$fieldName = 'NewFieldName';
		$fieldType = 'int';

		$this->assertArrayNotHasKey($fieldName, $fields);
		$migration = new \Tests\Fixtures\MigrationWrapper();
		$migration->addColumnTest('stringRecord', $fieldName, $fieldType);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(6, \count($fields));
		$this->assertArrayHasKey($fieldName, $fields);
		$this->assertTrue($fields[$fieldName]->nullable);
		$this->assertNull($fields[$fieldName]->defaultValue);
		$this->assertEquals($fieldType, \substr($fields[$fieldName]->type, 0, 3));	// ignore precision
		$migration->dropColumnTest('stringRecord', $fieldName);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));
		$this->assertArrayNotHasKey($fieldName, $fields);
		}

	public function testAlterColumn() : void
		{
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));

		$fieldName = 'stringDefaultNullable';
		$fieldNameNew = 'stringDefaultNullableNew';
		$this->assertArrayHasKey($fieldName, $fields);
		$this->assertFalse($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->primaryKey);
		$this->assertTrue($fields[$fieldName]->nullable);
		$this->assertEquals('default', $fields[$fieldName]->defaultValue);
		$this->assertEquals('varchar(100)', $fields[$fieldName]->type);

		if (\PHPFUI\ORM::getInstance()->sqlite)
			{
			return;	// alter table not supported
			}

		$migration = new \Tests\Fixtures\MigrationWrapper();

		$migration->alterColumnTest('stringRecord', $fieldName, 'varchar(255) not null default "123"');
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$migration->renameColumnTest('stringRecord', $fieldName, $fieldNameNew);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));

		$this->assertArrayHasKey($fieldNameNew, $fields);
		$this->assertArrayNotHasKey($fieldName, $fields);
		$this->assertFalse($fields[$fieldNameNew]->autoIncrement);
		$this->assertFalse($fields[$fieldNameNew]->primaryKey);
		$this->assertFalse($fields[$fieldNameNew]->nullable);
		$this->assertEquals('123', $fields[$fieldNameNew]->defaultValue);
		$this->assertEquals('varchar(255)', $fields[$fieldNameNew]->type);

		$migration->alterColumnTest('stringRecord', $fieldNameNew, 'varchar(10)');
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$migration->renameColumnTest('stringRecord', $fieldNameNew, $fieldName);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));
		$this->assertArrayHasKey($fieldName, $fields);
		$this->assertArrayNotHasKey($fieldNameNew, $fields);
		$this->assertFalse($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->primaryKey);
		$this->assertTrue($fields[$fieldName]->nullable);
		$this->assertNull($fields[$fieldName]->defaultValue);
		$this->assertEquals('varchar(10)', $fields[$fieldName]->type);

		$migration->alterColumnTest('stringRecord', $fieldName, "varchar(100) default 'default'");
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals(5, \count($fields));
		$this->assertArrayHasKey($fieldName, $fields);
		$this->assertFalse($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->primaryKey);
		$this->assertTrue($fields[$fieldName]->nullable);
		$this->assertEquals('default', $fields[$fieldName]->defaultValue);
		$this->assertEquals('varchar(100)', $fields[$fieldName]->type);
		}

	public function testDropAddIndex() : void
		{
		$table = 'supplier';
		$indexes = \PHPFUI\ORM::getIndexes($table);
		$this->assertEquals(6, \count($indexes));
		$migration = new \Tests\Fixtures\MigrationWrapper();
		$migration->dropIndexTest($table, 'supplier_zip_postal_code');
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$indexes = \PHPFUI\ORM::getIndexes($table);
		$this->assertEquals(5, \count($indexes));
		$migration->addIndexTest($table, ['zip_postal_code']);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$indexes = \PHPFUI\ORM::getIndexes($table);
		$this->assertEquals(6, \count($indexes));
		}

	public function testDropAddPrimaryKey() : void
		{
		$table = 'invoice';
		$fields = \PHPFUI\ORM::describeTable($table);
		$fieldName = $table . '_id';
		$this->assertTrue($fields[$fieldName]->primaryKey);
		$this->assertTrue($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->nullable);

		if (\PHPFUI\ORM::getInstance()->sqlite)
			{
			return;	// alter table not supported
			}

		$migration = new \Tests\Fixtures\MigrationWrapper();
		$migration->dropPrimaryKeyTest($table);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$fields = \PHPFUI\ORM::describeTable($table);
		$this->assertFalse($fields[$fieldName]->primaryKey);
		$this->assertFalse($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->nullable);

		$migration->addPrimaryKeyTest($table, [$fieldName]);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$fields = \PHPFUI\ORM::describeTable($table);
		$this->assertTrue($fields[$fieldName]->primaryKey);
		$this->assertFalse($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->nullable);

		$migration->dropPrimaryKeyTest($table);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$migration->addPrimaryKeyAutoIncrementTest($table);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$fields = \PHPFUI\ORM::describeTable($table);
		$this->assertTrue($fields[$fieldName]->primaryKey);
		$this->assertTrue($fields[$fieldName]->autoIncrement);
		$this->assertFalse($fields[$fieldName]->nullable);
		}

	public function testDropTable() : void
		{
		$table = 'setting';
		$tables = \PHPFUI\ORM::getTables();
		$this->assertContains($table, $tables);

		$migration = new \Tests\Fixtures\MigrationWrapper();
		$migration->dropTableTest($table);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$tables = \PHPFUI\ORM::getTables();
		$this->assertNotContains($table, $tables);

		$sql = 'CREATE TABLE `setting` (`setting_id` INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT, `setting_data` VARCHAR(255) NULL DEFAULT NULL);';
		if (\PHPFUI\ORM::getInstance()->sqlite)
			{
			$sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
			}
		$migration->runSQL($sql);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());

		$tables = \PHPFUI\ORM::getTables();
		$this->assertContains($table, $tables);
		}

	public function testFields() : void
		{
		$fields = \PHPFUI\ORM::describeTable('stringRecord');
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(5, \count($fields));

		$this->assertArrayHasKey('stringRecordId', $fields);
		$this->assertTrue($fields['stringRecordId']->autoIncrement);
		$this->assertTrue($fields['stringRecordId']->primaryKey);
		$this->assertFalse($fields['stringRecordId']->nullable);
		$this->assertEquals('int', \substr($fields['stringRecordId']->type, 0, 3));	// ignore precision

		$this->assertArrayHasKey('stringRequired', $fields);
		$this->assertFalse($fields['stringRequired']->autoIncrement);
		$this->assertFalse($fields['stringRequired']->primaryKey);
		$this->assertFalse($fields['stringRequired']->nullable);
		$this->assertEquals('varchar(100)', $fields['stringRequired']->type);

		$this->assertArrayHasKey('stringDefaultNull', $fields);
		$this->assertFalse($fields['stringDefaultNull']->autoIncrement);
		$this->assertFalse($fields['stringDefaultNull']->primaryKey);
		$this->assertTrue($fields['stringDefaultNull']->nullable);
		$this->assertNull($fields['stringDefaultNull']->defaultValue);
		$this->assertEquals('varchar(100)', $fields['stringDefaultNull']->type);

		$this->assertArrayHasKey('stringDefaultNullable', $fields);
		$this->assertFalse($fields['stringDefaultNullable']->autoIncrement);
		$this->assertFalse($fields['stringDefaultNullable']->primaryKey);
		$this->assertTrue($fields['stringDefaultNullable']->nullable);
		$this->assertEquals('default', $fields['stringDefaultNullable']->defaultValue);
		$this->assertEquals('varchar(100)', $fields['stringDefaultNullable']->type);

		$this->assertArrayHasKey('stringDefaultNotNull', $fields);
		$this->assertFalse($fields['stringDefaultNotNull']->autoIncrement);
		$this->assertFalse($fields['stringDefaultNotNull']->primaryKey);
		$this->assertFalse($fields['stringDefaultNotNull']->nullable);
		$this->assertEquals('default', $fields['stringDefaultNotNull']->defaultValue);
		$this->assertEquals('varchar(100)', $fields['stringDefaultNotNull']->type);
		}

	public function testForeignKeys() : void
		{
		$table = 'order';
		$keys = \PHPFUI\ORM::getForeignKeys($table);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(5, \count($keys));

		if (\PHPFUI\ORM::getInstance()->sqlite)
			{
			return;
			}

		$migration = new \Tests\Fixtures\MigrationWrapper();
		$firstKey = $keys[\array_key_first($keys)];
		$migration->dropForeignKeyTest($table, $firstKey->name);
		$migration->executeAlters();

		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$keys = \PHPFUI\ORM::getForeignKeys($table);
		$this->assertEquals(4, \count($keys));

		$migration->addForeignKeyTest($table, $firstKey->referencedTable, [$firstKey->referencedField], $firstKey->deleteRule, $firstKey->updateRule);
		$migration->executeAlters();
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$keys = \PHPFUI\ORM::getForeignKeys($table);
		$this->assertEquals(5, \count($keys));
		}

	public function testMigrations() : void
		{
		$tables = \PHPFUI\ORM::getTables();
		$this->assertContains('migration', $tables);
		$migrator = new \PHPFUI\ORM\Migrator();
		$this->assertEmpty($migrator->getErrors());
		$this->assertTrue($migrator->migrationNeeded());
		$this->assertEquals(1, $migrator->migrateUpOne());
		$this->assertEquals('Migrated to 1 successfully', $migrator->getStatus());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
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
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertCount(1, $migrationTable);
		$tables = \PHPFUI\ORM::getTables();
		$this->assertEquals(0, $migrator->migrateDownOne());
		$this->assertEquals('Migrated to 0 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertCount(0, $migrationTable);
		$this->assertEquals(3, $migrator->migrate());
		$this->assertEquals('Migrated to 3 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertCount(3, $migrationTable);
		$this->assertCount(3, $migrator);
		$this->assertEquals(3, $migrator->getCurrentMigrationId());
		$this->assertFalse($migrator->migrationNeeded());
		$migrator->migrateTo(1);
		$this->assertEquals('Migrated to 1 successfully', $migrator->getStatus());
		$this->assertEmpty($migrator->getErrors());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertCount(1, $migrationTable);
		$this->assertCount(3, $migrator->getMigrationObjects(0, 100));
		$this->assertCount(1, $migrator->getMigrationObjects(0, 1));
		$this->assertEquals(\Tests\Fixtures\Migration\Migration_1::class, \get_debug_type($migrator->getMigrationObject(1)));

		$migrator->migrateTo(-1);
		$this->assertCount(1, $migrator->getErrors());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertContains('Negative Migration Id (-1) is invalid', $migrator->getErrors());
		$migrator->migrateTo(4);
		$this->assertCount(2, $migrator->getErrors());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertContains('Target migration of 4 does not exist', $migrator->getErrors());
		}
	}
