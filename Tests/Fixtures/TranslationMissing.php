<?php

namespace Tests\Fixtures;

class TranslationMissing extends \PHPFUI\Translation\MissingLogger
{
	private array $missing = [];

	public function getMissing() : array
	{
		return $this->missing;
	}

	public function missing(string $missing, string $baseLocale) : string
	{
		$this->missing[$missing] = $baseLocale;

		return $missing;
	}
}
