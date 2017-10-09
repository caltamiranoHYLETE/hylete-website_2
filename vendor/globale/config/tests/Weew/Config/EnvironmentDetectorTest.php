<?php

namespace Tests\Weew\Config;

use PHPUnit_Framework_TestCase;
use Weew\Config\EnvironmentDetector;

class EnvironmentDetectorTest extends PHPUnit_Framework_TestCase {
    public function test_default_environments() {
        $detector = new EnvironmentDetector();

        $this->assertEquals('prod', $detector->detectEnvironment('prod'));
        $this->assertEquals('prod', $detector->detectEnvironment('_prod'));
        $this->assertEquals('prod', $detector->detectEnvironment('_prod.php'));
        $this->assertEquals('prod', $detector->detectEnvironment('foo_prod'));
        $this->assertEquals('prod', $detector->detectEnvironment('foo_prod.php'));
        $this->assertNull($detector->detectEnvironment('prodfoo'));
        $this->assertNull($detector->detectEnvironment('fooprod'));
        $this->assertNull($detector->detectEnvironment('_prodfoo'));

        $this->assertEquals('prod', $detector->detectEnvironment('production'));
        $this->assertEquals('prod', $detector->detectEnvironment('_production'));
        $this->assertEquals('prod', $detector->detectEnvironment('_production.php'));
        $this->assertEquals('prod', $detector->detectEnvironment('foo_production'));
        $this->assertEquals('prod', $detector->detectEnvironment('foo_production.php'));
        $this->assertNull($detector->detectEnvironment('productionfoo'));
        $this->assertNull($detector->detectEnvironment('fooproduction'));
        $this->assertNull($detector->detectEnvironment('_productionfoo'));

        $this->assertEquals('dev', $detector->detectEnvironment('dev'));
        $this->assertEquals('dev', $detector->detectEnvironment('_dev'));
        $this->assertEquals('dev', $detector->detectEnvironment('_dev.php'));
        $this->assertEquals('dev', $detector->detectEnvironment('foo_dev'));
        $this->assertEquals('dev', $detector->detectEnvironment('foo_dev.php'));
        $this->assertNull($detector->detectEnvironment('devfoo'));
        $this->assertNull($detector->detectEnvironment('foodev'));
        $this->assertNull($detector->detectEnvironment('_devfoo'));

        $this->assertEquals('dev', $detector->detectEnvironment('development'));
        $this->assertEquals('dev', $detector->detectEnvironment('_development'));
        $this->assertEquals('dev', $detector->detectEnvironment('_development.php'));
        $this->assertEquals('dev', $detector->detectEnvironment('foo_development'));
        $this->assertEquals('dev', $detector->detectEnvironment('foo_development.php'));
        $this->assertNull($detector->detectEnvironment('developmentfoo'));
        $this->assertNull($detector->detectEnvironment('foodevelopment'));
        $this->assertNull($detector->detectEnvironment('_developmentfoo'));

        $this->assertEquals('test', $detector->detectEnvironment('test'));
        $this->assertEquals('test', $detector->detectEnvironment('_test'));
        $this->assertEquals('test', $detector->detectEnvironment('_test.php'));
        $this->assertEquals('test', $detector->detectEnvironment('foo_test'));
        $this->assertEquals('test', $detector->detectEnvironment('foo_test.php'));
        $this->assertNull($detector->detectEnvironment('testfoo'));
        $this->assertNull($detector->detectEnvironment('footest'));
        $this->assertNull($detector->detectEnvironment('_testfoo'));

        $this->assertNull($detector->detectEnvironment('foo/bar'));
    }

    public function test_add_rule() {
        $detector = new EnvironmentDetector();
        $this->assertNull($detector->detectEnvironment('bar'));
        $this->assertNull($detector->detectEnvironment('foo_bar'));
        $this->assertNull($detector->detectEnvironment('_bar.php'));
        $this->assertNull($detector->detectEnvironment('foo_bar.php'));

        $detector->addRule('bar', ['bar']);

        $this->assertEquals('bar', $detector->detectEnvironment('bar'));
        $this->assertEquals('bar', $detector->detectEnvironment('foo_bar'));
        $this->assertEquals('bar', $detector->detectEnvironment('_bar.php'));
        $this->assertEquals('bar', $detector->detectEnvironment('foo_bar.php'));
    }
}
