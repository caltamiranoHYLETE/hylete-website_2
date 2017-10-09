<?php

namespace Weew\Contracts;

use Psr\Log\LoggerInterface;

interface ILoggable {
    /**
     * @param LoggerInterface $logger
     */
    function log(LoggerInterface $logger);
}
