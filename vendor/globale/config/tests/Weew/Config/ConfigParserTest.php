<?php

namespace Tests\Weew\Config;

use PHPUnit_Framework_TestCase;
use Weew\Config\Config;
use Weew\Config\ConfigParser;

class ConfigParserTest extends PHPUnit_Framework_TestCase {
    public function test_parse_string() {
        $parser = new ConfigParser();
        $config = new Config();

        $config->set('foo', '{name}');
        $config->set('bar', 'baz');
        $config->set('name', 'äüö');

        $this->assertEquals(
            'äüö is not baz',
            $parser->parse($config, '{foo} is not {bar}')
        );

        $this->assertEquals(1, $parser->parse($config, 1));
    }

    public function test_parse_array() {
        $parser = new ConfigParser();
        $config = new Config();

        $config->set('foo', '{name}');
        $config->set('bar', 'baz');
        $config->set('name', 'äüö');

        $this->assertEquals(
            [
                'bar' => 'äüö is not baz',
                'data' => [
                    'name' => 'äüöéè'
                ],
            ],
            $parser->parse($config, [
                'bar' => '{foo} is not {bar}',
                'data' => [
                    'name' => '{name}éè'
                ]
            ])
        );
    }

    public function test_parse_blocks() {
        $parser = new ConfigParser();
        $config = new Config();
        $config->set('foo', '{bar}');
        $config->set('bar', [
            'yolo' => 'swag',
        ]);

        $this->assertEquals(
            ['baz' => [
                'yolo' => 'swag'
            ]],
            $parser->parse($config, [
                'baz' => '{foo}'
            ])
        );
    }

    public function test_is_reference() {
        $parser = new ConfigParser();
        $this->assertEquals('foo.bar', $parser->parseReferencePath('{foo.bar}'));
        $this->assertNull($parser->parseReferencePath('{foo.bar'));
    }

    public function test_parse_reference_path() {
        $parser = new ConfigParser();
        $this->assertTrue($parser->isReference('{foo.bar}'));
        $this->assertFalse($parser->isReference('{foo.bar'));
        $this->assertFalse($parser->isReference('{foo.bar} {yolo.swag}'));
    }
}
