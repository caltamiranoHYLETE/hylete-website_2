<?php

namespace Weew\Config;

interface IEnvironmentDetector {
    /**
     * @param $string
     *
     * @return string|null
     */
    function detectEnvironment($string);

    /**
     * @param $name
     * @param array $abbreviations
     */
    function addRule($name, array $abbreviations);
}
