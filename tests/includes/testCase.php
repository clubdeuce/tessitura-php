<?php

namespace Clubdeuce\Tessitura\Tests;

use WP_UnitTestCase;

class testCase extends \PHPUnit\Framework\TestCase {

	protected function reflectionMethodInvoke(object $object, string $method, ...$args) {
		try {
			$reflection = new \ReflectionMethod($object::class, $method);
			return $reflection->invoke($object, $args);
		} catch (\ReflectionException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}

		return false;
	}

}
