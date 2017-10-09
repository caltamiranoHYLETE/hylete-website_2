<?php

namespace Weew\Config\Drivers;

use Weew\Config\Exceptions\InvalidConfigFormatException;
use Weew\Config\IConfigDriver;

class JsonConfigDriver implements IConfigDriver {
    /**
     * @param $path
     *
     * @return bool
     */
    public function supports($path) {
        return file_get_extension($path) == 'json';
    }

    /**
     * @param $path
     *
     * @return array
     * @throws InvalidConfigFormatException
     */
    public function loadFile($path) {
        $config = json_decode(file_get_contents($path), true);

        if ( ! is_array($config)) {;
            throw new InvalidConfigFormatException(
                s('JsonConfigDriver expects config at path %s to be a valid json file.', $path)
            );
        }

        return $config;
    }
}
