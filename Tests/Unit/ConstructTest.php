<?php

namespace Tests\Unit;

class ConstructTest extends \PHPUnit\Framework\TestCase
	{
	public function testArrayConstruct() : void
		{
		$sales_report = new \Tests\App\Record\SalesReport(['group_by' => 'Category', 'title' => 'Sales By Category']);
		$this->assertFalse($sales_report->empty());
		$this->assertTrue($sales_report->loaded());
		$this->assertEquals('SELECT DISTINCT [Category] FROM [products] ORDER BY [Category];', $sales_report->filter_row_source);
		}

	public function testDataObjectConstruct() : void
		{
		$order = new \Tests\App\Record\Order(30);
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
		$this->assertEquals('Karen Toh', $order->ship_name);
		$data = $order->toArray();
		$this->assertIsArray($data);
		$this->assertFalse(empty($data));
		$dataObject = new \PHPFUI\ORM\DataObject($data);
		$this->assertFalse($dataObject->empty());
		$clonedOrder = new \Tests\App\Record\Order($dataObject);
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
//INSERT INTO 'order' ('order_id', 'employee_id', 'customer_id', 'order_date', 'shipped_date', 'shipper_id', 'ship_name', 'ship_address', 'ship_city', 'ship_state_province', 'ship_zip_postal_code', 'ship_country_region', 'shipping_fee', 'taxes', 'payment_type', 'paid_date', 'notes', 'tax_rate', 'order_tax_status_id', 'order_status_id') VALUES
//(30, 9, 27, '2006-01-15 00:00:00', '2006-01-22 00:00:00', 2, 'Karen Toh', '789 27th Street', 'Las Vegas', 'NV', '99999', 'USA', 200, 0, 'Check', '2006-01-15 00:00:00', NULL, 0, NULL, 3);
		}

	public function testRecordConstruct() : void
		{
		$order = new \Tests\App\Record\Order(30);
		$this->assertFalse($order->empty());
		$this->assertTrue($order->loaded());
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
		$this->assertEquals('Karen Toh', $order->ship_name);
		}
	}
