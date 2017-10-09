<?php
namespace GlobalE\SDK\Models\Common\Response;

use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;

class Order extends Response
{
    /**
     * Order id
     * @var string
     * @access private
     */
	protected $OrderId;
    /**
     * Internal order id
     * @var string
     * @access private
     */
	protected $InternalOrderId;

    /**
     * Order constructor.
     * @param boolean $Success
     * @param string $Message
     * @param integer $OrderId
     * @param integer $InternalOrderId
     * @access public
     */
    public function __construct($Success, $Message, $OrderId, $InternalOrderId = null)
    {
        parent::__construct($Success, $Message);
        $this->setOrderId($OrderId);
        $this->setInternalOrderId($InternalOrderId);
    }

    /**
     * Get the order id
     * @return mixed
     * @access public
     */
    public function getOrderId()
    {
        return $this->OrderId;
    }

    /**
     * Set order id
     * @param string $OrderId
     * @return Order
     * @access public
     */
    public function setOrderId($OrderId)
    {
        $this->OrderId = $OrderId;
        return $this;
    }

    /**
     * Get the internal order id
     * @return string
     * @access public
     */
    public function getInternalOrderId()
    {
        return $this->InternalOrderId;
    }

    /**
     * Set the internal order id
     * @param string $InternalOrderId
     * @return Order
     * @access public
     */
    public function setInternalOrderId($InternalOrderId)
    {
        $this->InternalOrderId = $InternalOrderId;
        return $this;
    }

    /**
     * Get order response as string json format
     * @return string
     * @access public
     */
    public function __toString()
    {
        $StdObject = new \stdClass();
        $StdObject->Success = $this->getSuccess();
        $StdObject->Message = $this->getMessage();
        $StdObject->OrderId = $this->getOrderId();
        $StdObject->InternalOrderId = $this->getInternalOrderId();
        return Models\Json::encode($StdObject);
    }

}