<?php

class Ebizmarts_BakerlooRestful_Model_Api_Ping extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Process GET requests.
     *
     * @return type
     * @throws Exception
     */
    public function get() {
        return "Pong!";
    }

}