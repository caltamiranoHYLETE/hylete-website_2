<?php
namespace GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Json;

/**
 * Class Response
 * @package GlobalE\SDK\Models
 */
class Response {

    /**
     * Method success/unsuccess
     * @var bool
     * @access private
     */
    protected $Success;

    /**
     * Response message
     * @var string
     * @access private
     */
	protected $Message;

    /**
     * Response constructor.
     * @param bool $Success
     * @param string $Message
     * @access public
     */
    public function __construct($Success, $Message = null)
    {
        $this->setSuccess($Success);
        $this->setMessage($Message);
    }

    /**
     * Get method success/unsuccess
     * @return bool
     * @access public
     */
    public function getSuccess()
    {
        return $this->Success;
    }

    /**
     * Set method success/unsuccess
     * @param bool $Success
     * @return Response
     * @access public
     */
    public function setSuccess($Success)
    {
        $this->Success = $Success;
        return $this;
    }

    /**
     * Get the response message
     * @return string
     * @access public
     */
    public function getMessage()
    {
        return $this->Message;
    }

    /**
     * Set response message
     * @param string $Message
     * @return Response
     * @access public
     */
    public function setMessage($Message)
    {
        $this->Message = $Message;
        return $this;
    }

    /**
     * Get response object as json
     * @return string
     * @access public
     */
    public function __toString()
    {
        $StdObject = new \stdClass();
        $StdObject->Success = $this->getSuccess();
        $StdObject->Message = $this->getMessage();
        return Json::encode($StdObject);
    }

	/**
	 * return array of object variables
	 * @return array
	 */
    public function getObjectVars(){
    	return get_object_vars($this);
	}

}