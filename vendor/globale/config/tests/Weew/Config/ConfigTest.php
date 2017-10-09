<?php

namespace Tests\Weew\Config;

use PHPUnit_Framework_TestCase;
use Weew\Config\Config;
use Weew\Config\Exceptions\MissingConfigException;
use Weew\Config\IConfig;

class ConfigTest extends PHPUnit_Framework_TestCase {
    public function test_get_and_set_config() {
        $config = new Config(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $config->getConfig());
        $config->setConfig(['yolo' => 'swag']);
        $this->assertEquals(['yolo' => 'swag'], $config->getConfig());
    }

    public function test_to_array() {
        $config = new Config(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $config->toArray());
    }

    public function test_getters_and_setters() {
        $config = new Config([
            'foo' => 'bar',
            'yolo' => [
                'swag' => 'baz'
            ],
        ]);
        $this->assertEquals('bar', $config->get('foo'));
        $this->assertNull($config->get('bar'));
        $this->assertEquals('bar', $config->get('bar', 'bar'));
        $this->assertEquals('baz', $config->get('yolo.swag'));
        $config->set('a.nested.value', 'foo');
        $this->assertEquals('foo', $config->get('a.nested.value'));
        $this->assertFalse($config->has('baz'));
        $this->assertTrue($config->has('yolo.swag'));
        $config->remove('yolo.swag');
        $this->assertFalse($config->has('yolo.swag'));
    }

    public function test_merge() {
        $config = new Config([
            'foo' => 'bar',
            'baz' => [
                'yolo' => 'swag',
                'items' => [1, 2, 3, 4],
            ],
        ]);
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => [
                'yolo' => 'swag',
                'items' => [1, 2, 3, 4],
            ]
        ], $config->toArray());

        $config->merge([
            'foo' => 'swag',
            'baz' => [
                'foo' => 'bar',
                'items' => [9, 10],
            ],
        ]);
        $this->assertEquals([
            'foo' => 'swag',
            'baz' => [
                'yolo' => 'swag',
                'items' => [9, 10],
                'foo' => 'bar',
            ]
        ], $config->toArray());
    }

    public function test_extend() {
        $array1 = [
            'foo' => [
                'a' => 'b',
                'bar' => [1, 2, 3,]
            ]
        ];
        $array2 = [
            'foo' => [
                'x' => 'y',
                'bar' => [5],
            ],
        ];

        $config1 = new Config($array1);
        $config2 = new Config($array2);

        $config1->extend($config2);

        $this->assertEquals([
            'foo' => [
                'a' => 'b',
                'x' => 'y',
                'bar' => [5],
            ],
        ], $config1->toArray());
    }

    public function test_ensure_key_exists() {
        $config = new Config();
        $config->set('foo.bar', 'baz');

        $this->assertTrue($config->ensure('foo.bar') instanceof IConfig);
    }

    public function test_ensure_missing_key() {
        $config = new Config();

        $this->setExpectedException(MissingConfigException::class);
        $this->assertTrue($config->ensure('foo.bar') instanceof IConfig);
    }

    public function test_ensure_missing_key_with_custom_message() {
        $config = new Config();

        $this->setExpectedException(MissingConfigException::class);
        $this->assertTrue($config->ensure('foo.bar', 'foo') instanceof IConfig);
    }

    public function test_walks_over_arrays() {
        $config = new Config([
            'bar' => 'b',
            'baz' => 'z',
            'foo' => [
                '{bar} {baz}',
                ['{baz} {bar}']
            ],
            'yolo' => [
                'foo' => '{bar}',
            ],
        ]);

        $this->assertEquals(
            ['foo' => 'b'],
            $config->get('yolo')
        );

        $this->assertEquals(
            ['b z', ['z b']],
            $config->get('foo')
        );
    }

    public function test_get_absolute_config_key() {
        $config = new Config();
        $config->set('foo', '{bar.baz}');
        $config->set('bar', ['baz' => '{yolo.swag}']);
        $config->set('yolo.swag', ['key' => 'value']);

        $this->assertEquals(
            'yolo.swag.key', $config->getAbsoluteConfigKey('foo.key')
        );
    }

    public function test_get_with_reference() {
        $config = new Config();
        $config->set('foo', '{bar.baz}');
        $config->set('bar', ['baz' => '{yolo.swag}']);
        $config->set('yolo.swag', ['key' => 'value']);

        $this->assertEquals(['key' => 'value'], $config->get('foo'));
        $this->assertEquals('value', $config->get('foo.key'));
    }

    public function test_set_with_reference() {
        $config = new Config();
        $config->set('foo', '{bar.baz}');
        $config->set('bar', ['baz' => '{yolo.swag}']);
        $config->set('yolo.swag', ['key' => 'value']);

        $config->set('foo.key', 'secret');

        $this->assertEquals('secret', $config->get('foo.key'));
        $this->assertEquals('secret', $config->get('bar.baz.key'));
        $this->assertEquals('secret', $config->get('yolo.swag.key'));
    }

    public function test_has_with_reference() {
        $config = new Config();
        $config->set('foo', '{bar.baz}');
        $config->set('bar', ['baz' => '{yolo.swag}']);
        $config->set('yolo.swag', ['key' => 'value']);

        $this->assertTrue($config->has('foo.key'));
        $this->assertTrue($config->has('bar.baz.key'));
        $this->assertTrue($config->has('yolo.swag.key'));
    }

    public function test_remove_with_reference() {
        $config = new Config();
        $config->set('foo', '{bar.baz}');
        $config->set('bar', ['baz' => '{yolo.swag}']);
        $config->set('yolo.swag', ['key' => 'value']);

        $config->remove('foo.key');

        $this->assertFalse($config->has('foo.key'));
        $this->assertFalse($config->has('bar.baz.key'));
        $this->assertFalse($config->has('yolo.swag.key'));
    }
}
