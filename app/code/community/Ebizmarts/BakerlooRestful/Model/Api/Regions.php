<?php

class Ebizmarts_BakerlooRestful_Model_Api_Regions extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "directory/region";
    public $defaultSort = "country_id";

    //Avoid "SQLSTATE[HY093]: Invalid parameter number: no parameters were bound"
    protected $_iterator = false;

}