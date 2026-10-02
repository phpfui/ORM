<?php

namespace PHPFUI\ORM\PDO;

/**
 * Factory class to return the correct PDO subclass for the given dsn
 *
 * Example:
 *
 * $pdoInstance = \PHPFUI\ORM\PDO\Factory::get('sqlite:database.sqlite');
 */
class Factory
	{
	/**
	 * @param ?array<string, string|int|bool> $options
	 */
	public static function get(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null) : \PHPFUI\ORM\Interface\PDOInstance
		{
		if (\str_starts_with($dsn, 'pgsql'))
			{
			return new \PHPFUI\ORM\PDO\Pgsql($dsn, $username, $password, $options);
			}

		if (\str_starts_with($dsn, 'sqlite'))
			{
			return new \PHPFUI\ORM\PDO\Sqlite($dsn, $username, $password, $options);
			}

		return new \PHPFUI\ORM\PDO\Mysql($dsn, $username, $password, $options);
		}
	}
