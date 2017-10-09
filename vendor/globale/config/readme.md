# Configuration made simple

[![Build Status](https://img.shields.io/travis/weew/php-config.svg)](https://travis-ci.org/weew/php-config)
[![Code Quality](https://img.shields.io/scrutinizer/g/weew/php-config.svg)](https://scrutinizer-ci.com/g/weew/php-config)
[![Test Coverage](https://img.shields.io/coveralls/weew/php-config.svg)](https://coveralls.io/github/weew/php-config)
[![Version](https://img.shields.io/packagist/v/weew/php-config.svg)](https://packagist.org/packages/weew/php-config)
[![Licence](https://img.shields.io/packagist/l/weew/php-config.svg)](https://packagist.org/packages/weew/php-config)

## Table of contents

- [Installation](#installation)
- [Loading configurations](#loading-configurations)
- [Accessing configurations](#accessing-configurations)
- [Configuration formats](#configuration-formats)
- [References](#references)
- [Environments](#environments)
    - [Setting an environment](#setting-an-environment)
    - [How it works](#how-it-works)
    - [Adding custom environments](#adding-custom-environments)
- [Extending](#extending)
    - [Custom config drivers](#custom-config-drivers)
    - [Custom environment detector](#custom-environment-detector)

## Installation

`composer require weew/php-config`

## Loading configurations

The config loader is responsible for loading of the configuration files. All you have to do is to provide a location(file or directory) where it can find your configs. It will scan the locations(s) for configuration files and return you a config object.

```php
$loader = new ConfigLoader();
$loader->addPath('path/to/my/config');
$config = $loader->load();
```

## Accessing configurations

You can easily access config values by the config key.

```php
// get config value
$config->get('key', 'defaultValue');

// set config value
$config->set('key', 'value');

// check if config is set
$config->has('key');

// remove config value
$config->remove('key');

// check if config is set, throws MissingConfigException
$config
    ->ensure('key', 'errorMessage')
    ->ensure('anotherKey', 'errorMessage');
```

## Configuration formats

Following formats are supported out of the box.

Plain PHP format example:

```php
return [
    'key' => 'value',
    'list' => [
        'key' => 'value',
    ],
];
```
INI format example:

```ini
key = value

[list]
key = value
```

YAML format example:

```yaml
key: 'value'
list:
    key: value
```

Json format example:

```json
{
    "key": "value",
    "list": {
        "key": "value",
    }
}
```

## References

You can always reference to other config values from within a config file. To create a reference, simple wrap a config key with curly braces {config.key}`.

```php
// config1
return [
    'list' => [
        'foo' => 'bar'
    ]
];

// config2
return [
    'reference' => 'foo {list.foo}'
];

// returns 'foo bar'
$config->get('reference');
```

You can even reference whole config blocks.

```php
// config1
return [
    'list' => [
        'foo' => 'bar'
    ]
];

// config2
return [
    'reference' => '{list}'
];

// returns ['foo' => 'bar']
$config->get('reference');
```

Now when you access the `reference` value you will get "bar" in return. Keep in mind that references are interpolated at access time (when you call $config->get()). This means that if you change a config value, everyone who references it will receive it's updated value and not the old one.

## Environments

Often you want to split your configurations in multiple files or directories and load them depending on your current environment. This can be achieved trought the environment settings.

### Setting an environment

Out of the box it comes with support for dev, test and prod environments. Custom environments can be added on the fly.

```php
$loader->setEnviroonment('prod');
```

### How it works

To understand how environments detection works, lets take a look at this directory structure:

```
- test
    - db.php
- prod
    - db.php
- config.php
- config_test.php
- config_prod.php
```

In the test environment only the "test" directory and it's contents, "config\_test.php" and "config.php" will be loaded. In the prod environment however, it will load only the "prod" directory, "config\_prod.php" and "config.php".

Files and folders that have been added to the config loader will be loaded in the order of registration. Inside directories, files are loaded alphabetically.

### Adding custom environments

To create your own environments you'll have to register a new rule on the environment detector. The first argument is the name of the environment and the second is an array of masks.

```php
$loader->getEnvironmentDetector()
    ->addRule('integ', ['integ', 'integration', 'stage']);
```

Below is a list of some files and directories that would match the integration environment:

```
- stage
- _stage
- foo_stage
- _stage.txt
- foo_stage.txt
```

This files will not match:

```
- stagefoo
- foostage
- foo_stage_bar
- _stagebar
```

## Extending

Config loader provides you multiple extension points to alter its behaviour and functionality.

### Custom config drivers

Adding your own drivers is very easy. All you have to do is to implement the IConfigDriver interface and pass an instance of the driver to the config loader. You can have multiple active drivers at the same time.

```php
class MyDriver implements IConfigDriver {}

$loader->addDriver(new MyDriver());
```

### Custom environment detector

You can replace the default environment detector with your own. Just create a new detector which implements the IEnvironmentDetector interface and pass it to the config loader. Also take a look on how to [create your own environments](#adding-custom-environments).

```php
class MyDetector implements IEnvironmentDetector {}

$loader->setEnvironmentDetector(new MyDetector());
```
