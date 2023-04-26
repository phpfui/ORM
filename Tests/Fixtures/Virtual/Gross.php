<?php

namespace Tests\Fixtures\Virtual;

class Gross extends \PHPFUI\ORM\VirtualField
	{
	public function getValue(array $parameters) : mixed
		{
		return \number_format($this->currentRecord->unit_price * $this->currentRecord->quantity - $this->currentRecord->discount, 2);
		}
	}
