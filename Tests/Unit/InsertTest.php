<?php

namespace Tests\Unit;

class InsertTest extends \PHPUnit\Framework\TestCase
	{
	public function testDateNullInsert() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$test = new \Tests\App\Record\DateRecord();
		$test->dateRequired = $date = \date('Y-m-d');
		$timeStamp = \date('Y-m-d H:i:s');
		$id = $test->insert();
		$insertedTest = new \Tests\App\Record\DateRecord($id);
		$this->assertNull($insertedTest->dateDefaultNull);
		$this->assertEquals($date, $insertedTest->dateRequired);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNullable);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNotNull);
		$this->assertGreaterThanOrEqual($timeStamp, $insertedTest->timestampDefaultCurrentNullable);
		$this->assertGreaterThanOrEqual($timeStamp, $insertedTest->timestampDefaultCurrentNotNull);

		$this->assertTrue($transaction->rollBack());
		}

	public function testDateRequiredInsert() : void
		{
		$this->expectException(\Exception::class);
		$transaction = new \PHPFUI\ORM\Transaction();
		$test = new \Tests\App\Record\DateRecord();
		$id = $test->insert();
		$insertedTest = new \Tests\App\Record\DateRecord($id);
		$this->assertNull($insertedTest->dateDefaultNull);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNullable);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNotNull);
		$this->assertTrue($transaction->rollBack());
		}

	public function testRecordInsert() : void
		{
		$customer = new \Tests\App\Record\Customer();
		$customer->address = '123 Broadway';
		$customer->business_phone = '212-987-6543';
		$customer->city = 'New York';
		$customer->company = 'PHPFUI';
		$customer->country_region = 'USA';
		$customer->email_address = 'bruce@phpfui.com';
		$customer->fax_number = '212-345-6789';
		$customer->first_name = 'Bruce';
		$customer->home_phone = '987-654-3210';
		$customer->job_title = 'Head Honcho';
		$customer->last_name = 'Wells';
		$customer->mobile_phone = '123-456-7890';
		$customer->state_province = 'NY';
		$customer->web_page = 'http://www.phpfui.com';
		$customer->zip_postal_code = '10021';

		$table = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $table->count());
		$transaction = new \PHPFUI\ORM\Transaction();
		$this->assertEquals(30, $customer->insert());
		$this->assertEquals(30, $table->count());
		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $table->count());
		}

	public function testRelatedInsert() : void
		{
		$customerTable = new \Tests\App\Table\Customer();
		$this->assertEquals(29, $customerTable->count());
		$orderTable = new \Tests\App\Table\Order();
		$this->assertEquals(48, $orderTable->count());

		$transaction = new \PHPFUI\ORM\Transaction();

		$customer = new \Tests\App\Record\Customer();
		$customer->address = '123 Broadway';
		$customer->business_phone = '212-987-6543';
		$customer->city = 'New York';
		$customer->company = 'PHPFUI';
		$customer->country_region = 'USA';
		$customer->email_address = 'bruce@phpfui.com';
		$customer->fax_number = '212-345-6789';
		$customer->first_name = 'Bruce';
		$customer->home_phone = '987-654-3210';
		$customer->job_title = 'Head Honcho';
		$customer->last_name = 'Wells';
		$customer->mobile_phone = '123-456-7890';
		$customer->state_province = 'NY';
		$customer->web_page = 'http://www.phpfui.com';
		$customer->zip_postal_code = '10021';

		$order = new \Tests\App\Record\Order();
		$order->employee_id = 9;
		$order->customer = $customer;
		$this->assertEquals(30, $customerTable->count());
		$this->assertEquals(30, $order->customer_id);
		$date = \date('Y-m-d H:i:s');
		$order->order_date = $date;
		$shipper = new \Tests\App\Record\Shipper();
		$shipper->read(['company' => 'Shipping Company B']);
		$this->assertEquals(2, $shipper->shipper_id);
		$order->shipper = $shipper;
		$this->assertEquals($shipper->shipper_id, $order->shipper_id);
		$order->ship_name = $customer->company;
		$order->ship_address = $customer->address;
		$order->ship_city = $customer->city;
		$order->ship_state_province = $customer->state_province;
		$order->ship_zip_postal_code = $customer->zip_postal_code;
		$order->ship_country_region = $customer->country_region;
		$order->shipping_fee = 12.95;
		$order->taxes = 2.37;
		$order->payment_type = 'PO 54321';
		$order->notes = 'Test Order';
		$order->tax_rate = 5.25;
		$order->order_tax_status_id = 1;
		$order->order_status_id = 1;
		$this->assertEquals(82, $order->insert());
		$this->assertEquals(49, $orderTable->count());

		$orderTable->setWhere(new \PHPFUI\ORM\Condition('notes', 'Test Order'));
		$foundOrder = $orderTable->getRecordCursor()->current();

		$this->assertTrue($transaction->rollBack());
		$this->assertEquals(29, $customerTable->count());
		$orderTable->setWhere();
		$this->assertEquals(48, $orderTable->count());
		}

	public function testRequiredStringNotSetInsert() : void
		{
		$this->expectException(\Exception::class);
		$transaction = new \PHPFUI\ORM\Transaction();
		$test = new \Tests\App\Record\StringRecord();
		$id = $test->insert();
		$this->assertTrue($transaction->rollBack());
		}

	public function testStringNullInsert() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$test = new \Tests\App\Record\StringRecord();
		$test->stringRequired = $required = 'required';
		$id = $test->insert();
		$this->assertGreaterThan(0, $id);
		$insertedTest = new \Tests\App\Record\StringRecord($id);
		$this->assertNull($insertedTest->stringDefaultNull);
		$this->assertEquals($required, $insertedTest->stringRequired);
		$this->assertEquals('default', $insertedTest->stringDefaultNullable);
		$this->assertEquals('default', $insertedTest->stringDefaultNotNull);
		$this->assertTrue($transaction->rollBack());
		}
	}
