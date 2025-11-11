<?php

namespace Tests\Unit;

class MorphManyTest extends \PHPUnit\Framework\TestCase
	{
	public function testMorphMany() : void
		{
		$product = new \Tests\Fixtures\Record\Product(43);
		$this->assertTrue($product->loaded());
		$image = new \Tests\App\Record\Image();
		$image->path = 'product_1.jpg';
		$product->photos = $image;
		$this->assertCount(1, $product->photos);
		$this->assertTrue($image->loaded());
		$image = new \Tests\App\Record\Image();
		$image->path = 'product_2.jpg';
		$product->photos = $image;
		$this->assertCount(2, $product->photos);
		$this->assertTrue($image->loaded());

		foreach ($product->photos as $image)
			{
			$this->assertStringContainsString('product', $image->path);
			}

		$employee = new \Tests\Fixtures\Record\Employee(1);
		$this->assertTrue($employee->loaded());
		$image = new \Tests\App\Record\Image();
		$image->path = 'profile_1.jpg';
		$employee->photos = $image;
		$this->assertCount(1, $employee->photos);
		$this->assertTrue($image->loaded());
		$image = new \Tests\App\Record\Image();
		$image->path = 'profile_2.jpg';
		$employee->photos = $image;
		$this->assertCount(2, $employee->photos);
		$this->assertTrue($image->loaded());

		foreach ($employee->photos as $image)
			{
			$this->assertStringContainsString('profile', $image->path);
			}

		$imageTable = new \Tests\App\Table\Image();
		$this->assertCount(4, $imageTable);

		foreach ($imageTable as $image)
			{
			$this->assertStringContainsString('.jpg', $image->path);
			}

		$product->photos->current()->delete();
		$employee->photos->current()->delete();
		$this->assertCount(2, $imageTable);
		}
	}
