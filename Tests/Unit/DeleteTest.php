<?php

namespace Tests\Unit;

class DeleteTest extends \PHPUnit\Framework\TestCase
	{
	public function testRecordDelete() : void
		{
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$transaction = new \PHPFUI\ORM\Transaction();
		$customer = new \Tests\App\Record\Customer(9);
		$this->assertEquals('Company I', $customer->company);
		$customer->delete();
		$this->assertEquals(28, $table->count());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $table->count());
		}

	public function testTableDelete() : void
		{
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$transaction = new \PHPFUI\ORM\Transaction();
		$table->setWhere(new \PHPFUI\ORM\Condition('customer_id', 9));
		$table->delete();
		$this->assertEquals(0, $table->count());
		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(28, $table->count());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $table->count());
		}
	}
