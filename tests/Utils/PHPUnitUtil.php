<?php

declare(strict_types=1);

namespace Tests\Utils;

use ReflectionClass;

class PHPUnitUtil
{
    /**
     * Util to test call to private/protected method by reflection
     */
    public static function callMethod($obj, $name, array $args) {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        return $method->invokeArgs($obj, $args);
    }
}
