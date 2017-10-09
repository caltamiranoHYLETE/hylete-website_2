<?php

namespace Tests\Weew\Config\Drivers;

use PHPUnit_Framework_TestCase;
use Weew\Config\Drivers\YamlConfigDriver;

class YamlConfigDriverTest extends PHPUnit_Framework_TestCase {
    public function test_supports() {
        $driver = new YamlConfigDriver();
        $this->assertFalse($driver->supports('foo'));
        $this->assertTrue($driver->supports('foo.yml'));
    }

    public function test_load() {
        $path = path(__DIR__, '../configs/config.yml');
        $driver = new YamlConfigDriver();
        $config = $driver->loadFile($path);

        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'foo',
            'section' => [
                'yolo' => 'swag',
            ],
        ], $config);
    }
}
