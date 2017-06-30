<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_UploadLogo
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_UploadLogo_Block_Page_Html_Header extends Mage_Page_Block_Html_Header
{
    public function getLogoSrc()
    {
        if($path = Mage::getStoreConfig(Vaimo_UploadLogo_Helper_Data::XPATH_CONFIG_LOGO)) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "vaimo/uploadlogo/" . $path;
        } else {
            if (empty($this->_data['logo_src']))
                $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
            return $this->getSkinUrl($this->_data['logo_src']);
        }
    }
}