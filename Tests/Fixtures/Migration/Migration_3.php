<?php

namespace Tests\Fixtures\Migration;

class Migration_3 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Another dummy';
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
