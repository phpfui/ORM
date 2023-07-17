<?php

namespace Tests\Unit;

class ValidationTest extends \PHPUnit\Framework\TestCase
	{
	public function testAlpha() : void
		{
		$crud = new \Tests\Fixtures\Record\Alpha();
		$validator = new \Tests\Fixtures\Validation\Alpha($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->alpha = 'qweqqw';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->alpha = 'abc';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->alpha = '1234';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('alpha', $errors);
		$this->assertContains('1234 is not characters only', $errors['alpha']);

		$crud->alpha = '_!@#$%^&';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testAlphaNumeric() : void
		{
		$crud = new \Tests\Fixtures\Record\Alpha_numeric();
		$validator = new \Tests\Fixtures\Validation\Alpha_numeric($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->alpha_numeric = 'abc1234';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->alpha_numeric = '_!@#$%^&';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('alpha_numeric', $errors);
		$this->assertContains('_!@#$%^& is not number and characters only', $errors['alpha_numeric']);
		}

	public function testCard() : void
		{
		$crud = new \Tests\Fixtures\Record\Card();
		$validator = new \Tests\Fixtures\Validation\Card($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->card = '5105105105105100';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->card = '4111111111111111';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->card = '378282246310005';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->card = '5610591081018250';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->card = '12345657889';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('card', $errors);
		$this->assertContains('12345657889 is not a valid credit card number', $errors['card']);
		}

	public function testColor() : void
		{
		$crud = new \Tests\Fixtures\Record\Color();
		$validator = new \Tests\Fixtures\Validation\Color($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->color = '#777';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->color = '#7a7b7c';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->color = '#qwe';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('color', $errors);
		$this->assertContains('#qwe is not a valid color', $errors['color']);

		$crud->color = '#ff';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->color = '#ffff';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testComparison() : void
		{
		$crud = new \Tests\Fixtures\Record\Comparison();
		$validator = new \Tests\Fixtures\Validation\Comparison($crud);

		$date = '2023-01-01';
		$gtDate = '2023-01-02';
		$ltDate = '2022-12-31';

		$crud->equal = $date;
		$crud->not_equal = $gtDate;
		$crud->gt_field = $gtDate;
		$crud->gte_field = $date;
		$crud->lt_field = $ltDate;
		$crud->lte_field = $date;
		$crud->eq_field = $date;
		$crud->neq_field = $ltDate;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertEmpty($errors);

		$crud->equal = $date;
		$crud->not_equal = $gtDate;
		$crud->gt_field = $gtDate;
		$crud->gte_field = $date;
		$crud->lt_field = $ltDate;
		$crud->lte_field = $date;
		$crud->eq_field = $date;
		$crud->neq_field = $ltDate;
		$crud->date = $date;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertEmpty($errors);

		$crud->equal = $ltDate;
		$crud->not_equal = $date;
		$crud->gt_field = $date;
		$crud->gte_field = $ltDate;
		$crud->lt_field = $date;
		$crud->lte_field = $gtDate;
		$crud->eq_field = $ltDate;
		$crud->neq_field = $date;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertCount(8, $errors);
		$this->assertContains('2022-12-31 is not equal to 2023-01-01', $errors['equal']);
		$this->assertContains('2023-01-01 can not be equal to 2023-01-01', $errors['not_equal']);
		$this->assertContains('2023-01-01 is not greater than 2023-01-01 (date)', $errors['gt_field']);
		$this->assertContains('2022-12-31 is not greater than or equal to 2023-01-01 (date)', $errors['gte_field']);
		$this->assertContains('2023-01-01 is not less than 2023-01-01 (date)', $errors['lt_field']);
		$this->assertContains('2023-01-02 is not less than or equal to 2023-01-01 (date)', $errors['lte_field']);
		$this->assertContains('2022-12-31 is not equal to 2023-01-01 (date)', $errors['eq_field']);
		$this->assertContains('2023-01-01 can not be equal to 2023-01-01 (date)', $errors['neq_field']);
		}

	public function testCvv() : void
		{
		$crud = new \Tests\Fixtures\Record\Cvv();
		$validator = new \Tests\Fixtures\Validation\Cvv($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->cvv = '123';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->cvv = '1234';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->cvv = '0';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('cvv', $errors);
		$this->assertContains('0 is not a valid code', $errors['cvv']);

		$crud->cvv = '9';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->cvv = '99';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->cvv = '99999';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testDate() : void
		{
		$crud = new \Tests\Fixtures\Record\Date();
		$validator = new \Tests\Fixtures\Validation\Date($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['y', 'Y'] as $year)
			{
			foreach (['m', 'n'] as $month)
				{
				foreach (['d', 'j'] as $day)
					{
					foreach (\PHPFUI\ORM\Validator::$dateSeparators as $separator)
						{
						$crud->date = \date($year . $separator . $month . $separator . $day);
						$validator->validate();
						$this->assertEmpty($validator->getErrors(), "	{$crud->date} is not a valid date");
						}
					}
				}
			}

		// invalid tests
		$crud->date = '2020-2-30';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		// invalid tests
		$crud->date = '2020230';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('date', $errors);
		$this->assertContains('2020230 is not a valid date (Y-M-D)', $errors['date']);
		}

	public function testDateISO() : void
		{
		$crud = new \Tests\Fixtures\Record\DateISO();
		$validator = new \Tests\Fixtures\Validation\DateISO($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->dateISO = \date('Y-m-d');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->dateISO = \date('Y/m/d');
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->dateISO = \date('Y_m_d');
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->dateISO = \date('y_m_d');
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->dateISO = '2020-2-30';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('dateISO', $errors);
		$this->assertContains('2020-2-30 is not a valid ISO Date (YYYY-MM-DD)', $errors['dateISO']);

		$crud->dateISO = '2020230';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testDatetime() : void
		{
		$crud = new \Tests\Fixtures\Record\Datetime();
		$validator = new \Tests\Fixtures\Validation\Datetime($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->datetime = \date('Y-m-d') . 'T' . \date('H:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->datetime = \date('y-m-d') . 'T' . \date('H:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->datetime = \date('Y-m-d') . ' ' . \date('H:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->datetime = \date('y-m-d') . ' ' . \date('H:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$date = \date('m-d');
		$time = \date('H:i:2');
		$crud->datetime = $date . ' ' . $time;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('datetime', $errors);
		$this->assertContains($date . ' is not a valid date (Y-M-D)', $errors['datetime']);

		$crud->datetime = \date('Y-m-d');
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testDayMonthYear() : void
		{
		$crud = new \Tests\Fixtures\Record\Day_month_year();
		$validator = new \Tests\Fixtures\Validation\Day_month_year($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['y', 'Y'] as $year)
			{
			foreach (['m', 'n'] as $month)
				{
				foreach (['d', 'j'] as $day)
					{
					foreach (\PHPFUI\ORM\Validator::$dateSeparators as $separator)
						{
						$crud->day_month_year = \date($day . $separator . $month . $separator . $year);
						$validator->validate();
						$this->assertEmpty($validator->getErrors(), "	{$crud->day_month_year	} is not a valid day_month_year");
						}
					}
				}
			}

		// invalid tests
		$crud->day_month_year = '30-2-20';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('day_month_year', $errors);
		$this->assertContains('30-2-20 is not a valid date (D-M-Y)', $errors['day_month_year']);
		}

	public function testDomain() : void
		{
		$crud = new \Tests\Fixtures\Record\Domain();
		$validator = new \Tests\Fixtures\Validation\Domain($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->domain = 'www.ibm.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->domain = 'www.fred.fred';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->domain = 'fr ed';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('domain', $errors);
		$this->assertContains('fr ed is not a valid domain', $errors['domain']);

		$crud->domain = 'fr()ed';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->domain = 'frsdfhasdflasdfsadfjasldfjasfdjasfjsdlfjslfjslfjsdalfjasdldfjasdldfjsdajfsalkfjsalfjsadlkfjasdldfjsdf';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testEmail() : void
		{
		$crud = new \Tests\Fixtures\Record\Email();
		$validator = new \Tests\Fixtures\Validation\Email($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->email = 'test@test.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->email = 'test.test@test.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->email = '1test12@1test.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->email = '@test.com';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('email', $errors);
		$this->assertContains('@test.com is not a valid email address', $errors['email']);

		$crud->email = 'test@test';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->email = 'test ing@test.com';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testEnum() : void
		{
		$crud = new \Tests\Fixtures\Record\Enum();
		$validator = new \Tests\Fixtures\Validation\Enum($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['GET', 'post', 'PUT', 'DELETE'] as $value)
			{
			$crud->enum = $value;
			$validator->validate();
			$this->assertEmpty($validator->getErrors());
			}

		// invalid tests
		$crud->enum = 'fred';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('enum', $errors);
		$this->assertContains('fred is not one of GET,POST,PUT,DELETE', $errors['enum']);
		}

	public function testInteger() : void
		{
		$crud = new \Tests\Fixtures\Record\Integer();
		$validator = new \Tests\Fixtures\Validation\Integer($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->integer = 0;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->integer = -1;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->integer = 1;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		}

	public function testMaxlength() : void
		{
		$crud = new \Tests\Fixtures\Record\Maxlength();
		$validator = new \Tests\Fixtures\Validation\Maxlength($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->maxlength = '1234567890123456789';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->length = '12345678901234567890';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->length = '12';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->maxlength = '12345678901234567890';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('maxlength', $errors);
		$this->assertContains('Length is greater than 19', $errors['maxlength']);

		$crud->length = '123456789012345678901';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->length = '1';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testMaxvalue() : void
		{
		$crud = new \Tests\Fixtures\Record\Maxvalue();
		$validator = new \Tests\Fixtures\Validation\Maxvalue($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->maxvalue = 10;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->maxvalue = -10;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->maxvalue = '10';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->value = 15;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->value = 5;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->value = '15';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->value = '5';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->maxvalue = 11;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('maxvalue', $errors);
		$this->assertContains('11 is greater than 10', $errors['maxvalue']);

		$crud->maxvalue = '11';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->maxvalue = 'asd';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->value = 16;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->value = 4;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->value = '16';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->value = '4';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testMinlength() : void
		{
		$crud = new \Tests\Fixtures\Record\Minlength();
		$validator = new \Tests\Fixtures\Validation\Minlength($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->minlength = '1234567890123456789';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->minlength = '12345678901234567890';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->minlength = '123456789012345678';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->minlength = '12345';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('minlength', $errors);
		$this->assertContains('Length is less than 19', $errors['minlength']);
		}

	public function testMinvalue() : void
		{
		$crud = new \Tests\Fixtures\Record\Minvalue();
		$validator = new \Tests\Fixtures\Validation\Minvalue($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->minvalue = -10;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->minvalue = 10;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->minvalue = '-10';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->minvalue = '10';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->minvalue = -11;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('minvalue', $errors);
		$this->assertContains('-11 is less than -10', $errors['minvalue']);

		$crud->minvalue = '-100';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testMonthDayYear() : void
		{
		$crud = new \Tests\Fixtures\Record\Month_day_year();
		$validator = new \Tests\Fixtures\Validation\Month_day_year($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['y', 'Y'] as $year)
			{
			foreach (['m', 'n'] as $month)
				{
				foreach (['d', 'j'] as $day)
					{
					foreach (\PHPFUI\ORM\Validator::$dateSeparators as $separator)
						{
						$crud->month_day_year = \date($month . $separator . $day . $separator . $year);
						$validator->validate();
						$this->assertEmpty($validator->getErrors(), "	{$crud->month_day_year	} is not a valid month_day_year");
						}
					}
				}
			}

		// invalid tests
		$crud->month_day_year = '2-30-20';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('month_day_year', $errors);
		$this->assertContains('2-30-20 is not a valid date (M-D-Y)', $errors['month_day_year']);
		}

	public function testMonthYear() : void
		{
		$crud = new \Tests\Fixtures\Record\Month_year();
		$validator = new \Tests\Fixtures\Validation\Month_year($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['y', 'Y'] as $year)
			{
			foreach (['m', 'n'] as $month)
				{
				foreach (\PHPFUI\ORM\Validator::$dateSeparators as $separator)
					{
					$crud->month_year = \date($month . $separator . $year);
					$validator->validate();
					$this->assertEmpty($validator->getErrors(), "	{$crud->month_year	} is not a valid month_year");
					}
				}
			}

		// invalid tests
		$crud->month_year = 2020;
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('month_year', $errors);
		$this->assertContains('2020 is not a valid Month Year', $errors['month_year']);

		$crud->month_year = 12 - 2020;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->month_year = 12;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->month_year = 'asdfsad';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->month_year = '13-20';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->month_year = '13-2020';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testNumber() : void
		{
		$crud = new \Tests\Fixtures\Record\Number();
		$validator = new \Tests\Fixtures\Validation\Number($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->number = 1234.23432;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->number = 1234;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->number = '1234.23432';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->number = '1234';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->number = -1234.23432;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->number = 'abd';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('number', $errors);
		$this->assertContains('abd is not a valid number', $errors['number']);

		$crud->number = '#fff';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->number = '0x12';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->number = '12c123';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testOr() : void
		{
		$crud = new \Tests\Fixtures\Record\Alpha();
		$validator = new \Tests\Fixtures\Validation\LogicalOr($crud);

		$crud->alpha = '/1234';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertEmpty($errors);

		$crud->alpha = '1234';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertContains('1234 is not characters only', $errors['alpha']);
		$this->assertContains('1234 does not start with one of (/) of the same case', $errors['alpha']);
		}

	public function testRequired() : void
		{
		$crud = new \Tests\Fixtures\Record\Required();
		$validator = new \Tests\Fixtures\Validation\Required($crud);

		// empty test
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		// valid tests
		$crud->required = 'abc';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->required = 10;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->required = 0;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->required = 12.34;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->required = 0x23;
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->required = '';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('required', $errors);
		$this->assertContains('This field is required', $errors['required']);

		$crud->required = null;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testStrings() : void
		{
		$crud = new \Tests\Fixtures\Record\Strings();
		$crud->starts_with = 'asdfghjkl';
		$crud->ends_with = 'sdfghjklc';
		$crud->contains = 'sdfaghjkl';
		$crud->istarts_with = 'CSDFGHJKL';
		$crud->iends_with = 'SDFGHJKLB';
		$crud->icontains = 'SDFCGHJKL';
		$validator = new \Tests\Fixtures\Validation\Strings($crud);
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->starts_with = 'sdfghjkl';
		$crud->ends_with = 'sdfghjkl';
		$crud->contains = 'sdfghjkl';
		$crud->istarts_with = 'SDFGHJKL';
		$crud->iends_with = 'SDFGHJKL';
		$crud->icontains = 'SDFGHJKL';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertCount(6, $errors);
		$this->assertContains('sdfghjkl does not start with one of (a,b,c) of the same case', $errors['starts_with']);
		$this->assertContains('sdfghjkl does not end with one of (a,b,c) of the same case', $errors['ends_with']);
		$this->assertContains('sdfghjkl does not contain one of (a,b,c) of the same case', $errors['contains']);
		$this->assertContains('SDFGHJKL does not start with one of (a,b,c)', $errors['istarts_with']);
		$this->assertContains('SDFGHJKL does not end with one of (a,b,c)', $errors['iends_with']);
		$this->assertContains('SDFGHJKL does not contain one of (a,b,c)', $errors['icontains']);
		}

	public function testTime() : void
		{
		$crud = new \Tests\Fixtures\Record\Time();
		$validator = new \Tests\Fixtures\Validation\Time($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->time = \date('H:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = \date('H');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = \date('H:i');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = \date('h:i:s');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = \date('h');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = \date('h:i');
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '0:12:12';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '0:12';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '0';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '00';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '23:12:12';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '23:12';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->time = '23';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->time = '24';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('time', $errors);
		$this->assertContains('24 is not a valid time', $errors['time']);

		$crud->time = '24:12:12';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->time = '24:12';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->time = '12:72:12';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->time = '12:12:72';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->time = '123:12:12';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->time = 25;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testUnique() : void
		{
		$crud = new \Tests\Fixtures\Record\Product(90);
		$this->assertEquals('NWTCFV-90', $crud->product_code);
		$validator = new \Tests\Fixtures\Record\Validation\Product($crud);
		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());
		$crud->product_code = 'NWTCFV-91';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertCount(1, $errors);
		$this->assertContains('NWTCFV-91 is not unique', $errors['product_code']);
		}

	public function testUrl() : void
		{
		$crud = new \Tests\Fixtures\Record\Url();
		$validator = new \Tests\Fixtures\Validation\Url($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->url = 'ftp://www.google.com/test.php';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->url = 'ftp://www.google.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->url = 'https://www.google.com/test.php';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->url = 'test';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('url', $errors);
		$this->assertContains('test is not a valid URL', $errors['url']);
		}

	public function testWebsite() : void
		{
		$crud = new \Tests\Fixtures\Record\Website();
		$validator = new \Tests\Fixtures\Validation\Website($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		$crud->website = 'https://www.google.com/test';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->website = 'https://www.google.com/test';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		$crud->website = 'https://www.google.com';
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// invalid tests
		$crud->website = 'www.google.com';
		$validator->validate();
		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('website', $errors);
		$this->assertContains('www.google.com is not a valid website', $errors['website']);

		$crud->website = 'ftp://www.google.com';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}

	public function testYearMonth() : void
		{
		$crud = new \Tests\Fixtures\Record\Year_month();
		$validator = new \Tests\Fixtures\Validation\Year_month($crud);

		// empty test
		$validator->validate();
		$this->assertEmpty($validator->getErrors());

		// valid tests
		foreach (['y', 'Y'] as $year)
			{
			foreach (['m', 'n'] as $month)
				{
				foreach (\PHPFUI\ORM\Validator::$dateSeparators as $separator)
					{
					$crud->year_month = \date($year . $separator . $month);
					$validator->validate();
					$this->assertEmpty($validator->getErrors(), "	{$crud->year_month	} is not a valid year_month");
					}
				}
			}

		// invalid tests
		$crud->year_month = '2020';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$errors = $validator->getErrors();
		$this->assertNotEmpty($errors);
		$this->assertArrayHasKey('year_month', $errors);
		$this->assertContains('2020 is not a valid Year Month', $errors['year_month']);

		$crud->year_month = '2020-13';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->year_month = 2020 - 13;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->year_month = '20';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->year_month = '20-13';
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());

		$crud->year_month = 20 - 13;
		$validator->validate();
		$this->assertNotEmpty($validator->getErrors());
		}
	}
