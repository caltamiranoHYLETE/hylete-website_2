<?php
namespace GlobalE\SDK\Models\Common\Response;

use GlobalE\SDK\Models\Common\Response;

/**
 * Class Data
 * Common class of response data
 * @package GlobalE\SDK\Models\Response
 */
class Data extends Response
{
    /**
     * Response data
     * @var mixed
     * @access private
     */
	protected $Data;

    /**
     * Data constructor.
     * @param bool $Success
     * @param null|string $Data
     * @param null $Message
     * @access public
     */
    public function __construct($Success, $Data, $Message = null)
    {
        parent::__construct($Success, $Message);
        $this->setData($Data);
    }

    /**
     * Get the response data
     * @return mixed
     * @access public
     */
    public function getData()
    {
        return $this->Data;
    }

    /**
     * Set the response data
     * @param mixed $Data
     * @return Response\Data
     * @access public
     */
    public function setData($Data)
    {
        $this->Data = $Data;
        return $this;
    }

}