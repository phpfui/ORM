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
			$this->assertEquals($index++, $key, 'DataObjectCursor key is not correct');
			$this->assertEquals($index, $record->customer_id, 'DataObjectCursor record is not correct');
			$this->assertEquals($index, $record['customer_id'], 'DataObjectCursor array access is not correct');
			}
		}
	}
