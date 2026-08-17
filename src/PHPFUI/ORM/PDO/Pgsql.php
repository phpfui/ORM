<?php

namespace PHPFUI\ORM\PDO;

class Pgsql extends \Pdo\Pgsql implements \PHPFUI\ORM\Interface\PDOInstance
	{
	use \PHPFUI\ORM\Trait\PDOInstance;
	}
