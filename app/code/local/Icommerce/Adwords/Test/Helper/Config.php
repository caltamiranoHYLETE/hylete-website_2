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
 * Class Icommerce_Adwords_Test_Helper_Config
 *
 * Tests for Icommerce_Adwords_Helper_Config
 *
 * @group Icommerce_Adwords
 * @see Icommerce_Adwords_Helper_Config
 */
class Icommerce_Adwords_Test_Helper_Config extends EcomDev_PHPUnit_Test_Case
{

    /** Magento Helper alias */
    const HELPER_ALIAS = 'adwords/config';

    /**
     * Tests for Icommerce_Adwords_Helper_Config::getVerticals()
     *
     * @test
     * @loadFixture
     * @see Icommerce_Adwords_Helper_Config::getVerticals()
     */
    public function getVerticals()
    {
        /** @var array $verticalsFromFixture */
        $verticalsFromFixture = Mage::getStoreConfig('adwords/verticals');

        /** @var Icommerce_Adwords_Helper_Config $helper */
        $helper = $this->_getHelper();

        /** @var array $verticalsFromHelper */
        $verticalsFromHelper = $helper->getVerticals();

        $this->assertSame($verticalsFromFixture, $verticalsFromHelper);
    }

    /**
     * Tests for Icommerce_Adwords_Helper_Config::getCurrentVertical()
     *
     * @test
     * @loadFixture
     * @see Icommerce_Adwords_Helper_Config::getCurrentVertical()
     */
    public function getCurrentVertical()
    {
        /** @var string $currentVerticalFromFixture */
        $currentVerticalFromFixture = Mage::getStoreConfig('adwords/settings/google_remarketing_vertical');

        /** @var Icommerce_Adwords_Helper_Config $helper */
        $helper = $this->_getHelper();

        /** @var string $currentVerticalFromHelper */
        $currentVerticalFromHelper = $helper->getCurrentVertical();

        $this->assertSame($currentVerticalFromFixture, $currentVerticalFromHelper);

    }

    /**
     * Tests for Icommerce_Adwords_Helper_Config::getSettings()
     *
     * @test
     * @loadFixture
     * @see Icommerce_Adwords_Helper_Config::getSettings()
     */
    public function getSettings()
    {
        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfig('adwords/settings/some/random/path');

        /** @var Icommerce_Adwords_Helper_Config $helper */
        $helper = $this->_getHelper();

        /** @var string $currentVerticalFromHelper */
        $valueFromHelper = $helper->getSettings('some/random/path');

        $this->assertSame($valueFromFixture, $valueFromHelper);

    }

    /**
     * @return Icommerce_Adwords_Helper_Config
     */
    protected function _getHelper()
    {
        /** @var Icommerce_Adwords_Helper_Config $helper */
        $helper = Mage::helper(self::HELPER_ALIAS);

        return $helper;
    }
}
