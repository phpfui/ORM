<?php

namespace Tests\Unit;

class ChildrenTest extends \PHPUnit\Framework\TestCase
	{
	public function testChildren() : void
		{
		$order = new \Tests\Fixtures\Record\Order(44);
		$this->assertTrue($order->loaded());
		$orderDetails = $order->OrderDetailChildren;
		$this->assertCount(3, $orderDetails);
		$this->assertEquals(25.0 * 18.0, $orderDetails->current()->gross);
		}
	}
