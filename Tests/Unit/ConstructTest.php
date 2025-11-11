<?php

namespace Tests\Unit;

class ConstructTest extends \PHPUnit\Framework\TestCase
	{
	public function testArrayConstruct() : void
		{
		$sales_report = new \Tests\App\Record\SalesReport(['group_by' => 'Category', 'title' => 'Sales By Category']);
		$this->assertFalse($sales_report->empty());
		$this->assertTrue($sales_report->loaded());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('SELECT DISTINCT [Category] FROM [products] ORDER BY [Category];', $sales_report->filter_row_source);
		}

	public function testDataObjectConstruct() : void
		{
		$order = new \Tests\App\Record\Order(30);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
		$this->assertEquals('Karen Toh', $order->ship_name);
		$data = $order->toArray();
		$this->assertIsArray($data);
		$this->assertFalse(empty($data));
		$dataObject = new \PHPFUI\ORM\DataObject($data);
		$this->assertFalse($dataObject->empty());
		$clonedOrder = new \Tests\App\Record\Order($dataObject);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertFalse($clonedOrder->empty());
		$this->assertFalse($clonedOrder->loaded());
		$this->assertEquals('Karen Toh', $clonedOrder->ship_name);
		$clonedOrder->ship_name = 'Fred Flintstone';
		$this->assertEquals('Fred Flintstone', $clonedOrder->ship_name);
		$this->assertEquals('Karen Toh', $order->ship_name);
		}

	public function testEnptyConstruct() : void
		{
		$order = new \Tests\App\Record\Order();
		$this->assertTrue($order->empty());
		$this->assertFalse($order->loaded());
		$order = new \Tests\App\Record\Order(1);
		$this->assertTrue($order->empty());
		$this->assertFalse($order->loaded());
		}

	public function testIntConstruct() : void
		{
		$order = new \Tests\App\Record\Order(30);
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
		$this->assertEquals('Karen Toh', $order->ship_name);
		}

	public function testRecordConstruct() : void
		{
		$order = new \Tests\App\Record\Order(30);
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('Karen Toh', $order->ship_name);
		$clonedOrder = new \Tests\App\Record\Order($order);
		$this->assertFalse($clonedOrder->empty());
		$this->assertFalse($clonedOrder->loaded());
		$this->assertEquals('Karen Toh', $clonedOrder->ship_name);
		$clonedOrder->ship_name = 'Fred Flintstone';
		$this->assertEquals('Fred Flintstone', $clonedOrder->ship_name);
		$this->assertEquals('Karen Toh', $order->ship_name);
		}

	public function testStringConstruct() : void
		{
		$order = new \Tests\App\Record\Order('30');
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals('Karen Toh', $order->ship_name);
		}
	}
