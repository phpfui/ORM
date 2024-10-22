<?php

namespace Tests\Unit;

class RecordTest extends \PHPUnit\Framework\TestCase
	{
	public function testBadField() : void
		{
		$order = new \Tests\Fixtures\Record\Order();
		$this->assertFalse($order->loaded());
		$this->assertFalse(empty($order->ship_address));
		$order->ship_address = '';
		$this->assertTrue(empty($order->ship_address));
		$this->assertTrue(empty($order->fred));
		$this->assertFalse(isset($order->fred));
		$this->expectException(\PHPFUI\ORM\Exception::class);
		$order->fred = 'Fred';
		}

	public function testEmpty() : void
		{
		$order = new \Tests\Fixtures\Record\Order(30);
		$this->assertTrue($order->loaded());
		$this->assertFalse(empty($order->employee));
		$order->employee_id = 0;
		$this->assertTrue(empty($order->employee));
		$this->assertTrue(empty($order->employee_id));
		$order->employee = new \Tests\Fixtures\Record\Employee(1);
		$this->assertFalse(empty($order->employee));
		$this->assertFalse(empty($order->employee_id));
		$order->employee = new \Tests\Fixtures\Record\Employee();
		$this->assertTrue(empty($order->employee));
		$this->assertTrue(empty($order->employee_id));
		}
	}
