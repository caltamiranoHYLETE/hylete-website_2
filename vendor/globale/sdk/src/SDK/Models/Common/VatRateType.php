<?php
namespace GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common;

/**
 * Class VatRateType
 * @method getVATRateTypeCode()
 * @method getName()
 * @method getRate()
 * @method $this setVATRateTypeCode($VATRateTypeCode)
 * @method $this setName($Name)
 * @method $this setRate($Rate)
 * @package GlobalE\SDK\Models\Common
 */
class VatRateType extends Common {

    /**
     * Vat rate type code
     * @var string
     * @access public
     */
    public $VATRateTypeCode;
    /**
     * Vat rate name
     * @var string
     * @access public
     */
    public $Name;
    /**
     * Vat rate amount
     * @var float
     * @access public
     */
    public $Rate;

    /**
     * VatRateType constructor, requires rate, name and vat rate type code, those are mandatory fields for this common.
     * @param $Rate
     * @param $Name
     * @param $VATRateTypeCode
     * @access public
     */
    public function __construct($Rate, $Name, $VATRateTypeCode){

        $this->setVATRateTypeCode($VATRateTypeCode)
             ->setRate($Rate)
             ->setName($Name);
    }

}