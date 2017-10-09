<?php

namespace Weew\Contracts;

interface IUnArrayable {
    /**
     * @param array $array
     */
    static function fromArray(array $array);
}
