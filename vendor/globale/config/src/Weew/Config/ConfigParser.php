<?php

namespace Weew\Config;

class ConfigParser implements IConfigParser {
    /**
     * @param IConfig $config
     * @param $value
     *
     * @return mixed
     */
    public function parse(IConfig $config, $value) {
        if (is_array($value)) {
            return $this->parseArray($config, $value);
        } else if (is_string($value)) {
            return $this->parseString($config, $value);
        }

        return $this->parseAbstract($config, $value);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isReference($value) {
        if (is_string($value)) {
            return preg_match($this->getReferenceRegexPattern(), $value) === 1;
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    public function parseReferencePath($value) {
        if (preg_match($this->getReferenceRegexPattern(), $value, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param IConfig $config
     * @param array $array
     *
     * @return array
     */
    protected function parseArray(IConfig $config, array $array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->parse($config, $value);
        }

        return $array;
    }

    /**
     * @param IConfig $config
     * @param $string
     *
     * @return mixed
     */
    protected function parseString(IConfig $config, $string) {
        if ($this->isReference($string)) {
            return $config->get($this->parseReferencePath($string));
        } else {
            $string = preg_replace_callback($this->getInterpolationRegexPattern(), function($matches) use ($config) {
                return $config->get($matches[1]);
            }, $string);

            return $string;
        }
    }

    /**
     * @param IConfig $config
     * @param $abstract
     *
     * @return mixed
     */
    protected function parseAbstract(IConfig $config, $abstract) {
        return $abstract;
    }

    /**
     * @return string
     */
    protected function getReferenceRegexPattern() {
        return '#^\{([^\}]+)\}$#';
    }

    /**
     * @return string
     */
    protected function getInterpolationRegexPattern() {
        return '#\{([^\}]+)\}#';
    }
}
