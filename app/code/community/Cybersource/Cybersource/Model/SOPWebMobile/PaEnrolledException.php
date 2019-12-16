<?php

class Cybersource_Cybersource_Model_SOPWebMobile_PaEnrolledException extends Exception
{
    /**
     * @var array
     */
    private $details;

    public function __construct($message = "", $details = array(), $code = 0, Throwable $previous = null)
    {
        $this->details = $details;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }
}
