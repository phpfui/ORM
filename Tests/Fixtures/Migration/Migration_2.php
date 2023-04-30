<?php

namespace Tests\Fixtures\Migration;

class Migration_2 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Dummy #2';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		return true;
		}
	}
