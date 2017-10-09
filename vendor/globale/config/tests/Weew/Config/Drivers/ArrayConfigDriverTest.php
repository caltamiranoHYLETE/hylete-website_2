<?php

namespace Tests\Weew\Config\Drivers;

use PHPUnit_Framework_TestCase;
use Weew\Config\Drivers\ArrayConfigDriver;
use Weew\Config\Exceptions\InvalidConfigFormatException;

class ArrayConfigDriverTest extends PHPUnit_Framework_TestCase {
    public function test_supports() {
        $driver = new ArrayConfigDriver();
        $this->assertTrue($driver->supports('foo.php'));
        $this->assertFalse($driver->supports('foo.html'));
    }

    public function test_exception_gets_thrown_when_loading_a_invalid_file() {
        $path = path(__DIR__, '/../configs/bad_array.php');
        $driver = new ArrayConfigDriver();
        $this->setExpectedException(InvalidConfigFormatException::class);
        $driver->loadFile($path);
    }

    public function test_load_file() {
        $path = path(__DIR__, '/../configs/array.php');
        $driver = new ArrayConfigDriver();
        $config = $driver->loadFile($path);
        $this->assertTrue(get_type($config) == 'array');
        $this->assertEquals(require $path, $config);
    }
}
