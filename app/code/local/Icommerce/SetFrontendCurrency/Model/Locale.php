<?php
/**
 * Copyright (c) 2009-2012 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Module
 * @copyright   Copyright (c) 2009-2012 Vaimo AB
 */

class Icommerce_SetFrontendCurrency_Model_Locale extends Mage_Core_Model_Locale
{
    protected $precision_level = false;

    public function __construct($locale = null)
    {
        parent::__construct($locale);
        $this->_construct();
    }

    protected function _construct()
    {
        $precision_is_active = Icommerce_Default::getStoreConfig('setfrontendcurrency/settings/precision_active');
        if ($precision_is_active) {
            $this->precision_level = (int )Icommerce_Default::getStoreConfig('setfrontendcurrency/settings/precision');
        }
        return $this;
    }
    public function getJsPriceFormat()
    {
        $arr = parent::getJsPriceFormat();
        if ($this->precision_level !== false) {
            $arr['requiredPrecision'] = $this->precision_level;
        }

        return $arr;
    }
}
