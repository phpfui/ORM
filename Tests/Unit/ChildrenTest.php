<?php

namespace Tests\Unit;

class ChildrenTest extends \PHPUnit\Framework\TestCase
	{
	public function testChildren() : void
		{
		$order = new \Tests\Fixtures\Record\Order(44);
		$this->assertTrue($order->loaded());
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$orderDetails = $order->orderDetailChildren;
		$this->assertCount(3, $orderDetails);
		$this->assertEquals('', \PHPFUI\ORM::getLastError());
		$this->assertEquals(25.0 * 18.0, $orderDetails->current()->gross);
		}
	}
