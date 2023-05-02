<?php

namespace Tests\Unit;

class CastTest extends \PHPUnit\Framework\TestCase
	{
	public function testOrderCast() : void
		{
		$order = new \Tests\Fixtures\Record\Order(30);
		$this->assertTrue($order->loaded());
		$this->assertEquals(7, $order->order_date->diffInDays($order->shipped_date));
		$ship_date = $order->shipped_date->addDays(10);
		$order->shipped_date = $ship_date;
		$this->assertEquals(17, $order->order_date->diffInDays($order->shipped_date));
		}
	}
