<?php

namespace Tests\Unit;

class UpdateTest extends \PHPUnit\Framework\TestCase
	{
	public function testRecordUpdate() : void
		{
		$customerTable = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $customerTable->count());
		$condition = new \PHPFUI\ORM\Condition('last_name', 'Wells');
		$condition->or(new \PHPFUI\ORM\Condition('first_name', 'Bruce'));
		$customerTable->setWhere($condition);
		$this->assertEquals(0, $customerTable->count());

		$customerTable2 = new \Tests\App\Table\Customer();
		$condition2 = new \PHPFUI\ORM\Condition('last_name', 'Kupkova');
		$condition2->and(new \PHPFUI\ORM\Condition('first_name', 'Helena'));
		$customerTable2->setWhere($condition2);
		$this->assertEquals(1, $customerTable2->count());

		$transaction = new \PHPFUI\ORM\Transaction();
		$customer = new \Tests\App\Record\Customer(15);
		$this->assertEquals('Helena', $customer->first_name);
		$this->assertEquals('Kupkova', $customer->last_name);
		$customer->first_name = 'Bruce';
		$customer->last_name = 'Wells';
		$customer->update();
		$this->assertEquals(1, $customerTable->count());
		$this->assertEquals(0, $customerTable2->count());

		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(0, $customerTable->count());
		$this->assertEquals(1, $customerTable2->count());
		$customerTable->setWhere();
		$this->assertEquals(29, $customerTable->count());
		}
	}
