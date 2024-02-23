<?php

namespace Tests\Fixtures\Record;

class Employee extends \Tests\App\Record\Definition\Employee
	{
	protected static array $virtualFields = [
		'photos' => [\PHPFUI\ORM\MorphMany::class, \Tests\App\Table\Image::class, 'imageable', ],
	];
	}
