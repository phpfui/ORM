<?php

namespace Tests\Unit;

class ManyToManyTest extends \PHPUnit\Framework\TestCase
	{
	public function testManyToMany() : void
		{
		$product = new \Tests\Fixtures\Record\Product(43);
		$this->assertTrue($product->loaded());
		$suppliers = $product->suppliers;
		$this->assertCount(2, $suppliers);
		$this->assertEquals('Supplier C', $suppliers->current()->company);
		}
	}
