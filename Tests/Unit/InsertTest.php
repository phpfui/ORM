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

		$this->assertNull($insertedTest->dateDefaultNull, 'dateDefaultNull is not null');
		$this->assertEquals($date, $insertedTest->dateRequired, 'dateRequired does not equal ' . $date);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNullable);
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNotNull);
		$this->assertGreaterThanOrEqual($timeStamp, $insertedTest->timestampDefaultCurrentNullable, 'timestampDefaultCurrentNullable is greater than ' . $timeStamp);
		$this->assertGreaterThanOrEqual($timeStamp, $insertedTest->timestampDefaultCurrentNotNull, 'timestampDefaultCurrentNotNull is wrong');

		$this->assertTrue($transaction->rollBack());
		}

	public function testDateRequiredInsert() : void
		{
		$test = new \Tests\App\Record\DateRecord();
		$id = $test->insert();
		$this->assertNotEmpty(\PHPFUI\ORM::getLastError());
		$this->assertEquals(0, $id, '$id is not zero');
		$insertedTest = new \Tests\App\Record\DateRecord($id);
		$this->assertNull($insertedTest->dateDefaultNull, 'dateDefaultNull is not after insert');
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNullable, 'dateDefaultNullable has bad default value');
		$this->assertEquals('2000-01-02', $insertedTest->dateDefaultNotNull, 'dateDefaultNotNull has bad default value');

		}

	public function testMultipleInserts() : void
		{
		$transaction = new \PHPFUI\ORM\Transaction();
		$customer1 = new \Tests\App\Record\Customer();
		$customer1->address = '123 Broadway';
		$customer1->business_phone = '212-987-6543';
		$customer1->city = 'New York';
		$customer1->company = 'PHPFUI';
		$customer1->country_region = 'USA';
		$customer1->email_address = 'bruce@phpfui.com';
		$customer1->fax_number = '212-345-6789';
		$customer1->first_name = 'Bruce';
		$customer1->home_phone = '987-654-3210';
		$customer1->job_title = 'Head Honcho';
		$customer1->last_name = 'Wells';
		$customer1->mobile_phone = '123-456-7890';
		$customer1->state_province = 'NY';
		$customer1->web_page = 'http://www.phpfui.com';
		$customer1->zip_postal_code = '10021';

		$customer2 = new \Tests\App\Record\Customer();
		$customer2->address = '123 Main Street';
		$customer2->business_phone = '212-555-1212';
		$customer2->city = 'New York City';
		$customer2->company = 'PHPFUI';
		$customer2->country_region = 'USA';
		$customer2->email_address = 'bruce2@phpfui.com';
		$customer2->fax_number = '212-111-3333';
		$customer2->first_name = 'Bruce';
		$customer2->home_phone = '987-654-3210';
		$customer2->job_title = 'Head Honcho';
		$customer2->last_name = 'Wells';
		$customer2->state_province = 'NY';
		$customer2->web_page = 'http://buriedtreasure.phpfui.com';

		$customer3 = new \Tests\App\Record\Customer();
		$customer3->address = '456 Elm';
		$customer3->business_phone = '212-987-6543';
		$customer3->city = 'Rochester';
		$customer3->company = 'PHPFUI';
		$customer3->email_address = 'bruce3@phpfui.net';
		$customer3->fax_number = '212-345-6789';
		$customer3->first_name = 'Fred';
		$customer3->home_phone = '987-654-3210';
		$customer3->job_title = 'Honcho';
		$customer3->last_name = 'Willis';
		$customer3->mobile_phone = '123-456-7890';
		$customer3->state_province = 'NY';

		$customers = [];
		$customers[] = $customer1;
		$customers[] = $customer2;
		$customers[] = $customer3;

		$customerTable = new \Tests\App\Table\Customer();
		$this->assertCount(29, $customerTable);

		$customerTable->insert($customers);
		$this->assertCount(32, $customerTable);
		$customerTable->setWhere(new \PHPFUI\ORM\Condition('zip_postal_code', operator:new \PHPFUI\ORM\Operator\IsNull()));
		$this->assertCount(2, $customerTable);
		$customerTable->setWhere(new \PHPFUI\ORM\Condition('email_address', '%@phpfui%', new \PHPFUI\ORM\Operator\Like()));
		$this->assertCount(3, $customerTable);

		$this->assertTrue($transaction->rollBack());
		$customerTable->setWhere();
		$this->assertCount(29, $customerTable);
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
		$test = new \Tests\App\Record\StringRecord();
		$id = $test->insert();
		$this->assertNotEmpty(\PHPFUI\ORM::getLastError());
		$this->assertEquals(0, $id);
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
