<?php

namespace Weew\Config\Drivers;

use Exception;
use Weew\Config\Exceptions\InvalidConfigFormatException;
use Weew\Config\IConfigDriver;

class IniConfigDriver implements IConfigDriver {
    /**
     * @param $path
     *
     * @return bool
     */
    public function supports($path) {
        return file_get_extension($path) == 'ini';
    }

    /**
     * @param $path
     *
     * @return array
     * @throws InvalidConfigFormatException
     */
    public function loadFile($path) {
        $config = null;
        $mode = defined('INI_SCANNER_TYPED') ? INI_SCANNER_TYPED : INI_SCANNER_NORMAL;

        try {
            $config = parse_ini_file($path, true, $mode);
        } catch (Exception $ex) {}

        if ( ! is_array($config)) {
            throw new InvalidConfigFormatException(
                s('IniConfigDriver expects config at path %s to be a valid ini file.', $path)
            );
        }

        return $config;
    }
}
