<?php

namespace Weew\Config;

use Weew\Config\Drivers\ArrayConfigDriver;
use Weew\Config\Drivers\IniConfigDriver;
use Weew\Config\Drivers\YamlConfigDriver;

class ConfigLoader implements IConfigLoader {
    /**
     * @var array
     */
    protected $paths;

    /**
     * @var IConfigDriver[]
     */
    protected $drivers;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var IEnvironmentDetector
     */
    protected $detector;

    /**
     * @param string $environment
     * @param array $paths
     * @param IConfigDriver[]|null $drivers
     * @param IEnvironmentDetector $detector
     */
    public function __construct(
        $environment = null,
        array $paths = [],
        array $drivers = null,
        IEnvironmentDetector $detector = null
    ) {
        if ($environment === null) {
            $environment = 'dev';
        }

        if ($drivers === null) {
            $drivers = $this->createDefaultConfigDrivers();
        }

        if ( ! $detector instanceof IEnvironmentDetector) {
            $detector = $this->createEnvironmentDetector();
        }

        $this->setEnvironment($environment);
        $this->setPaths($paths);
        $this->setDrivers($drivers);
        $this->setEnvironmentDetector($detector);
    }

    /**
     * @param IConfig $config
     *
     * @return Config
     */
    public function load(IConfig $config = null) {
        if ( ! $config instanceof IConfig) {
            $config = $this->createConfig();
        }

        $configs = [];

        foreach ($this->getPaths() as $path) {
            $configs[] = $this->loadPath($path);
        }

        $config->merge(
            $this->processConfiguration($configs)
        );

        return $config;
    }

    /**
     * @return mixed
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * @param $environment
     */
    public function setEnvironment($environment) {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getPaths() {
        return $this->paths;
    }

    /**
     * @param array $paths
     */
    public function setPaths(array $paths) {
        $this->paths = $paths;
    }

    /**
     * @param $path
     */
    public function addPath($path) {
        $this->paths[] = $path;
    }

    /**
     * @param array $paths
     */
    public function addPaths(array $paths) {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * @return IConfigDriver[]
     */
    public function getDrivers() {
        return $this->drivers;
    }

    /**
     * @param IConfigDriver[] $drivers
     */
    public function setDrivers(array $drivers) {
        $this->drivers = [];

        foreach ($drivers as $driver) {
            $this->addDriver($driver);
        }
    }

    /**
     * @param IConfigDriver $driver
     */
    public function addDriver(IConfigDriver $driver) {
        $this->drivers[] = $driver;
    }

    /**
     * @param array $drivers
     */
    public function addDrivers(array $drivers) {
        foreach ($drivers as $driver) {
            $this->addDriver($driver);
        }
    }

    /**
     * @return IEnvironmentDetector
     */
    public function getEnvironmentDetector() {
        return $this->detector;
    }

    /**
     * @param IEnvironmentDetector $detector
     */
    public function setEnvironmentDetector(IEnvironmentDetector $detector) {
        $this->detector = $detector;
    }

    /**
     * @return EnvironmentDetector
     */
    public function createEnvironmentDetector() {
        return new EnvironmentDetector();
    }

    /**
     * @param $path
     *
     * @return array
     */
    protected function loadPath($path) {
        if (directory_exists($path)) {
            return $this->loadDirectory($path);
        } else if (file_exists($path)) {
            return $this->loadFile($path);
        }

        return [];
    }

    /**
     * @param $path
     *
     * @return array
     */
    protected function loadFile($path) {
        $path = realpath($path);

        if ($this->matchEnvironment($path)) {
            foreach ($this->getDrivers() as $driver) {
                if ($driver->supports($path)) {
                    return $driver->loadFile($path);
                }
            }
        }

        return [];
    }

    /**
     * @param $path
     *
     * @return array
     */
    protected function loadDirectory($path) {
        $configs = [];

        if ($this->matchEnvironment($path)) {
            $files = directory_list_files($path);
            $directories = directory_list_directories($path);

            $files = array_merge($files, $directories);

            foreach ($files as $file) {
                $nextPath = path($path, $file);
                $configs[] = $this->loadPath($nextPath);
            }
        }

        return $this->processConfiguration($configs);
    }

    /**
     * @return array
     */
    protected function createDefaultConfigDrivers() {
        return [
            new ArrayConfigDriver(),
            new IniConfigDriver(),
            new YamlConfigDriver()
        ];
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    protected function processConfiguration(array $configs) {
        $config = [];

        foreach ($configs as $group) {
            $config = array_extend_distinct($config, $group);
        }

        return $config;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    protected function matchEnvironment($path) {
        $env = $this->getEnvironmentDetector()
            ->detectEnvironment(basename($path));

        if ($env === null || $env == $this->getEnvironment()) {
            return true;
        }

        return false;
    }

    /**
     * @return Config
     */
    protected function createConfig() {
        return new Config();
    }
}
