<?php

namespace Tests\Fixtures\Validation;

class Card extends \PHPFUI\ORM\Validator
	{
	public static array $validators = [
		'card' => ['card'],
		'not_card' => ['!card'],
	];
	}
