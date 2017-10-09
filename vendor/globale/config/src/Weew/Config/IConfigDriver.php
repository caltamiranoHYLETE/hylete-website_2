<?php

namespace Weew\Config;

interface IConfigDriver {
    /**
     * @param $path
     *
     * @return bool
     */
    function supports($path);

    /**
     * @param $path
     *
     * @return array
     */
    function loadFile($path);
}
