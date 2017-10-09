<?php

namespace Weew\Contracts;

interface IUnJsonable {
    /**
     * @param $json
     */
    static function fromJson($json);
}
