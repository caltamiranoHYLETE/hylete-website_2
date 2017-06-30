<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @package     Icommerce_Adwords
 * @copyright   Copyright (c) 2009-2015 Vaimo Norge AS
 * @author      Simen Thorsrud <simen.thorsrud@vaimo.com>
 */

/**
 * Class Icommerce_Adwords_Helper_Config
 */
class Icommerce_Adwords_Helper_Config extends Mage_Core_Helper_Abstract
{

    /** Xpath to Adwords settings */
    const XPATH_ADWORDS_SETTINGS = 'adwords/settings';

    /**
     * Get Google Adwords remarketing vertical as defined in config
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @return array
     */
    public function getVerticals()
    {
        /** @var null|array $verticalsFromConfig */
        $verticalsFromConfig = Mage::getStoreConfig('adwords/verticals');

        // I want the return type to be consistent
        if (!array($verticalsFromConfig)) {
            $verticalsFromConfig = array();
        }

        return $verticalsFromConfig;
    }

    /**
     * Get current value of Google Adwords Remarketing vertical
     *
     * @link https://support.google.com/adwords/answer/3103357?hl=en
     * @link https://developers.google.com/adwords-remarketing-tag/parameters?hl=en
     * @return bool|mixed
     */
    public function getCurrentVertical()
    {
        return $this->getSettings('google_remarketing_vertical');
    }

    /**
     * Get adwords settings from "settings" node in Adwords config
     *
     * @param string     $path
     * @param bool|false $configFlag
     * @return bool|mixed
     */
    public function getSettings($path = '', $configFlag = false)
    {
        $path = trim($path, '/');

        $path = self::XPATH_ADWORDS_SETTINGS . '/' . $path;

        if ($configFlag) {
            $configValue = Mage::getStoreConfigFlag($path);
        } else {
            $configValue = Mage::getStoreConfig($path);
        }

        return $configValue;
    }
}
