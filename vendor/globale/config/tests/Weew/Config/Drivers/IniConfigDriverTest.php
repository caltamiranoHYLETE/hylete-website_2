<?php

namespace Tests\Weew\Config\Drivers;

use PHPUnit_Framework_TestCase;
use Weew\Config\Drivers\IniConfigDriver;
use Weew\Config\Exceptions\InvalidConfigFormatException;

class IniConfigDriverTest extends PHPUnit_Framework_TestCase {
    public function test_supports() {
        $driver = new IniConfigDriver();
        $this->assertFalse($driver->supports('foo'));
        $this->assertTrue($driver->supports('foo.ini'));
    }

    public function test_load_file() {
        $path = path(__DIR__, '../configs/config.ini');
        $driver = new IniConfigDriver();
        $config = $driver->loadFile($path);

        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'foo',
            'section' => ['yolo' => 2],
        ], $config);
    }

    public function test_load_invalid_file() {
        $path = path(__DIR__, '../configs/foo.ini');
        $driver = new IniConfigDriver();

        $this->setExpectedException(InvalidConfigFormatException::class);
        $driver->loadFile($path);
    }
}
