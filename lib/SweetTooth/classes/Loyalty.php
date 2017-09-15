<?php

class SweetToothLoyalty
{
    private $prefix = "/loyalty";
    private $client;

    public function __construct($client) {
        $this->client = $client;
    }

    public function get() {
        $result = $this->client->get($this->prefix);
        return $this->client->prepareResponse($result);
    }

    public function __destruct(){
        unset($this->prefix);
    }
}
