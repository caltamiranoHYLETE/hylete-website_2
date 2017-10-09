<?php

namespace Weew\Contracts;

interface IJsonable {
    /**
     * @param int $options
     *
     * @return string
     */
    function toJson($options = 0);
}
