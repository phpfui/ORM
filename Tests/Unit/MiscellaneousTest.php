<?php

namespace Tests\Unit;

class MiscellaneousTest extends \PHPUnit\Framework\TestCase
	{
	public function testNoStringPrimaryKey() : void
		{
		$customer = new \Tests\App\Record\Customer(1);
		$this->assertTrue($customer->loaded());
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$customer = new \Tests\App\Record\Customer('test');
		}

	public function testRow() : void
		{
		$row = \PHPFUI\ORM::getRow('select * from customer');
		$this->assertIsArray($row);
		$this->assertCount(18, $row);
		$this->assertArrayHasKey('company', $row);
		$this->assertEquals('Company A', $row['company']);
		}

	public function testRows() : void
		{
		$rows = \PHPFUI\ORM::getRows('select * from customer');
		$this->assertIsArray($rows);
		$this->assertCount(29, $rows);
		$this->assertEquals('Company A', $rows[0]['company']);
		}

	public function testValue() : void
		{
		$row = \PHPFUI\ORM::getValue('select * from customer');
		$this->assertIsNotArray($row);
		$this->assertEquals(1, $row);
		}

	public function testValueArray() : void
		{
		$rows = \PHPFUI\ORM::getValueArray('select * from customer');
		$this->assertIsArray($rows);
		$this->assertCount(29, $rows);
		$this->assertEquals(1, $rows[0]);
		}
	}
