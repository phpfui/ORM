<?php

namespace Tests\Fixtures\Enum;

enum ProductStatus : int
	{
	use \Tests\Fixtures\Enum\Name;

	case ACTIVE = 0;
	case BACKORDERED = 1;
	case DISCONTINUED = 2;
	}

