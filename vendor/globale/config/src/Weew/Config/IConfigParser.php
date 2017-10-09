<?php

namespace Weew\Config;

interface IConfigParser {
    /**
     * @param IConfig $config
     * @param $value
     *
     * @return mixed
     */
    function parse(IConfig $config, $value);

    /**
     * @param $value
     *
     * @return bool
     */
    function isReference($value);

    /**
     * @param $value
     *
     * @return string
     */
    function parseReferencePath($value);
}
