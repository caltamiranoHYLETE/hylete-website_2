<?php

namespace Weew\Config\Drivers;

use Exception;
use Weew\Config\Exceptions\InvalidConfigFormatException;
use Weew\Config\IConfigDriver;

class ArrayConfigDriver implements IConfigDriver {
    /**
     * @param $path
     *
     * @return bool
     */
    public function supports($path) {
        return file_get_extension($path) == 'php';
    }

    /**
     * @param $path
     *
     * @return array
     * @throws InvalidConfigFormatException
     */
    public function loadFile($path) {
        $config = null;

        try {
            $config = require $path;
        } catch (Exception $ex) {}

        if ( ! is_array($config)) {
            throw new InvalidConfigFormatException(
                s('ArrayConfigDriver expects config at path %s to be of format array[key => value].', $path)
            );
        }

        return $config;
    }
}
