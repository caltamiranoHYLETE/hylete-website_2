<?php

namespace Weew\Config;

use Weew\Config\Exceptions\MissingConfigException;

class Config implements IConfig {
    /**
     * @var array
     */
    protected $config;

    /**
     * @var IConfigParser
     */
    protected $parser;

    /**
     * @param array $config
     * @param IConfigParser $parser
     */
    public function __construct(array $config = [], IConfigParser $parser = null) {
        if ( ! $parser instanceof IConfigParser) {
            $parser = $this->createConfigParser();
        }

        $this->setConfigParser($parser);
        $this->setConfig($config);
    }

    /**
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config) {
        $this->config = $config;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public function get($key, $default = null) {
        $key = $this->getAbsoluteConfigKey($key);

        return $this->getConfigParser()
            ->parse($this, array_get($this->config, $key, $default));
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) {
        $key = $this->getAbsoluteConfigKey($key);

        array_set($this->config, $key, $value);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key) {
        $key = $this->getAbsoluteConfigKey($key);

        return array_has($this->config, $key);
    }

    /**
     * @param $key
     */
    public function remove($key) {
        $key = $this->getAbsoluteConfigKey($key);

        array_remove($this->config, $key);
    }

    /**
     * @param array $config
     */
    public function merge(array $config) {
        $this->setConfig(
            array_extend_distinct($this->getConfig(), $config)
        );
    }

    /**
     * @param IConfig $config
     */
    public function extend(IConfig $config) {
        $this->merge($config->getConfig());
    }

    /**
     * @param $key
     * @param null $errorMessage
     *
     * @return IConfig
     * @throws MissingConfigException
     */
    public function ensure($key, $errorMessage = null) {
        if ( ! $this->has($key)) {
            if ($errorMessage === null) {
                $errorMessage = sprintf('Missing config at key "%s".', $key);
            } else {
                $errorMessage = s('%s: %s', $key, $errorMessage);
            }

            throw new MissingConfigException($errorMessage);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->getConfigParser()
            ->parse($this, $this->getConfig());
    }

    /**
     * @return ConfigParser
     */
    protected function createConfigParser() {
        return new ConfigParser();
    }

    /**
     * @return IConfigParser
     */
    public function getConfigParser() {
        return $this->parser;
    }

    /**
     * @param IConfigParser $parser
     */
    public function setConfigParser(IConfigParser $parser) {
        $this->parser = $parser;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getAbsoluteConfigKey($key) {
        $prefix = null;
        $parser = $this->getConfigParser();
        $steps = explode('.', $key);
        $config = $this->config;

        foreach ($steps as $step) {
            if ( ! is_array($config)) {
                break;
            }

            array_shift($steps);
            $config = array_get($config, $step);

            if ($parser->isReference($config)) {
                array_unshift($steps, $parser->parseReferencePath($config));
                $config = implode('.', $steps);

                return $this->getAbsoluteConfigKey($config);
            }
        }

        return $key;
    }
}
