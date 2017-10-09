<?php

namespace Weew\Config;

interface IConfigLoader {
    /**
     * @param IConfig $config
     *
     * @return Config
     */
    function load(IConfig $config = null);

    /**
     * @return string
     */
    function getEnvironment();

    /**
     * @param $environment
     */
    function setEnvironment($environment);

    /**
     * @return IEnvironmentDetector
     */
    function getEnvironmentDetector();

    /**
     * @param IEnvironmentDetector $detector
     */
    function setEnvironmentDetector(IEnvironmentDetector $detector);

    /**
     * @return array
     */
    function getPaths();

    /**
     * @param array $paths
     */
    function setPaths(array $paths);

    /**
     * @param $path
     */
    function addPath($path);

    /**
     * @param array $paths
     */
    function addPaths(array $paths);

    /**
     * @return IConfigDriver[]
     */
    function getDrivers();

    /**
     * @param IConfigDriver[] $drivers
     */
    function setDrivers(array $drivers);

    /**
     * @param IConfigDriver $driver
     */
    function addDriver(IConfigDriver $driver);

    /**
     * @param array $drivers
     */
    function addDrivers(array $drivers);
}
