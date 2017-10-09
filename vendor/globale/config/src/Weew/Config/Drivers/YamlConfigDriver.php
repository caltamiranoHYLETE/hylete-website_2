<?php

namespace Weew\Config\Drivers;

use Symfony\Component\Yaml\Yaml;
use Weew\Config\IConfigDriver;

class YamlConfigDriver implements IConfigDriver {
    /**
     * @param $path
     *
     * @return bool
     */
    public function supports($path) {
        return file_get_extension($path) == 'yml';
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function loadFile($path) {
        return Yaml::parse(file_get_contents($path));
    }
}
