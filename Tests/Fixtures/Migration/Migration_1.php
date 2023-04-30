<?php

namespace Tests\Fixtures\Migration;

class Migration_1 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Crash Test Dummy';
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
