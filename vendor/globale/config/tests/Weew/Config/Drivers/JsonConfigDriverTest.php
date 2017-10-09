<?php

namespace Tests\Weew\Config\Drivers;

use PHPUnit_Framework_TestCase;
use Weew\Config\Drivers\JsonConfigDriver;
use Weew\Config\Exceptions\InvalidConfigFormatException;

class JsonConfigDriverTest extends PHPUnit_Framework_TestCase {
    public function test_supports() {
        $driver = new JsonConfigDriver();
        $this->assertFalse($driver->supports('foo'));
        $this->assertTrue($driver->supports('foo.json'));;
    }

    public function test_load_file() {
        $path = path(__DIR__, '../configs/config.json');
        $driver = new JsonConfigDriver();
        $config = $driver->loadFile($path);

        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'foo',
            'section' => ['yolo' => 'swag'],
        ], $config);
    }

    public function test_load_invalid_file() {
        $path = path(__DIR__, '../configs/bad_config.json');
        $driver = new JsonConfigDriver();
        $this->setExpectedException(InvalidConfigFormatException::class);
        $driver->loadFile($path);
    }
}
