<?php

namespace Tests\Unit;

class CursorTest extends \PHPUnit\Framework\TestCase
	{
	public function testCursor() : void
		{
		$table = new \Tests\App\Table\Customer();
		$table->addOrderBy('customer_id');

		$index = 0;

		foreach ($table->getRecordCursor() as $key => $record)
			{
			$this->assertEquals($index++, $key, 'RecordCursor key is not correct');
			$this->assertEquals($index, $record->customer_id, 'RecordCursor record is not correct');
			$this->assertEquals($index, $record['customer_id'], 'RecordCursor array access is not correct');
			}

		$index = 0;

		foreach ($table->getArrayCursor() as $key => $record)
			{
			$this->assertEquals($index++, $key, 'ArrayCursor key is not correct');
			$this->assertEquals($index, $record['customer_id'], 'ArrayCursor record is not correct');
			}

		$index = 0;

		foreach ($table->getDataObjectCursor() as $key => $record)
			{
			$customer = new \Tests\App\Record\Customer($record);
			$this->assertFalse($customer->empty(), 'Record constructed from DataObject is empty');
			$this->assertEquals($index++, $key, 'DataObjectCursor key is not correct');
			$this->assertEquals($index, $record->customer_id, 'DataObjectCursor record is not correct');
			$this->assertEquals($index, $record['customer_id'], 'DataObjectCursor array access is not correct');
			}
		}

	public function testCursorOnEnum() : void
		{
		$table = new \Tests\Fixtures\Table\Product();

		foreach ($table->getRecordCursor() as $key => $record)
			{
			$this->assertTrue($record->discontinued instanceof \Tests\Fixtures\Enum\ProductStatus, 'discontinued (type ' . \get_debug_type($record->discontinued) . ') is not a ProductStatus enum');
			}
		}
	}
