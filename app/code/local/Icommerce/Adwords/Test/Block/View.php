<?php
/**
 * Copyright (c) 2009-2015 Vaimo Norge AS
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
 * Class Icommerce_Adwords_Test_Block_View
 *
 * Tests for Icommerce_Adwords_Block_View
 *
 * @see Icommerce_Adwords_Block_View
 * @group Icommerce_Adwords
 */
class Icommerce_Adwords_Test_Block_View extends EcomDev_PHPUnit_Test_Case_Controller
{

    /** Magento block alias */
    const BLOCK_ALIAS = 'adwords/view';

    /** @var Vaimo_PHPUnit_Helper_Data */
    protected $_testHelper;

    /**
     * Tests for Icommerce_Adwords_Block_View::isActive()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::isActive()
     */
    public function isActive()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var bool $activeFromFixture */
        $activeFromFixture = Mage::getStoreConfigFlag('adwords/settings/active');

        /** @var bool $activeFromBlock */
        $activeFromBlock = $block->isActive();

        $this->assertSame($activeFromFixture, $activeFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionId()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionId()
     */
    public function getGoogleConversionId()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $conversionIdFromFixture */
        $conversionIdFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_id');

        /** @var string $conversionIdFromBlock */
        $conversionIdFromBlock = $block->getGoogleConversionId();

        $this->assertSame($conversionIdFromFixture, $conversionIdFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionLanguage()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionLanguage()
     */
    public function getGoogleConversionLanguage()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $languageFromFixture */
        $languageFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_language');

        /** @var string $languageFromBlock */
        $languageFromBlock = $block->getGoogleConversionLanguage();

        $this->assertSame($languageFromFixture, $languageFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionFormat()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionFormat()
     */
    public function getGoogleConversionFormat()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $formatFromFixture */
        $formatFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_format');

        /** @var string $formatFromBlock */
        $formatFromBlock = $block->getGoogleConversionFormat();

        $this->assertSame($formatFromFixture, $formatFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionColor()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionColor()
     */
    public function getGoogleConversionColor()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_color');

        /** @var string $valueFromBlock */
        $valueFromBlock = $block->getGoogleConversionColor();

        $this->assertSame($valueFromFixture, $valueFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionLabel()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionLabel()
     */
    public function getGoogleConversionLabel()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_label');

        /** @var string $valueFromBlock */
        $valueFromBlock = $block->getGoogleConversionLabel();

        $this->assertSame($valueFromFixture, $valueFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleConversionValue()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleConversionValue()
     */
    public function getGoogleConversionValue()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfig('adwords/settings/google_conversion_value');

        /** @var string $valueFromBlock */
        $valueFromBlock = $block->getGoogleConversionValue();

        $this->assertSame($valueFromFixture, $valueFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::isGoogleRemarketingActive()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::isGoogleRemarketingActive()
     */
    public function isGoogleRemarketingActive()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfigFlag('adwords/settings/google_remarketing_active');

        /** @var string $valueFromBlock */
        $valueFromBlock = $block->isGoogleRemarketingActive();

        $this->assertSame($valueFromFixture, $valueFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleRemarketingOnly()
     *
     * @loadFixture
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleRemarketingOnly()
     */
    public function getGoogleRemarketingOnly()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var string $valueFromFixture */
        $valueFromFixture = Mage::getStoreConfigFlag('adwords/settings/google_remarketing_only');

        /** @var string $valueFromBlock */
        $valueFromBlock = $block->getGoogleRemarketingOnly();

        $this->assertSame($valueFromFixture, $valueFromBlock);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleTagParams()
     *
     * @test
     * @see Icommerce_Adwords_Block_View::getGoogleTagParams()
     */
    public function getGoogleTagParams()
    {
        /** @var Icommerce_Adwords_Block_View $blockMock */
        $blockMock = $this->getBlockMock(
            'adwords/view',
            array('_getRemarketingModel', 'getGoogleCustomTagParams', '_getJson')
        );

        $blockMock->expects($this->once())->method('_getJson');
        $blockMock->method('getGoogleCustomTagParams')->willReturn(array('2'));
        $blockMock->expects($this->once())->method('getGoogleCustomTagParams');

        /** @var Icommerce_Adwords_Model_Remarketing_Vertical_Retail $remarketingModelMock */
        $remarketingModelMock = $this->getModelMock(
            'adwords/remarketing_vertical_retail',
            array('getGoogleTagParamsArray')
        );

        $remarketingModelMock->method('getGoogleTagParamsArray')->willReturn(array('1'));
        $remarketingModelMock->expects($this->once())->method('getGoogleTagParamsArray');

        $blockMock->method('_getRemarketingModel')->willReturn($remarketingModelMock);
        $blockMock->expects($this->once())->method('_getRemarketingModel');

        $blockMock->getGoogleTagParams();
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::_getJson()
     *
     * @dataProvider dataProvider
     * @loadExpectations
     * @test
     * @see Icommerce_Adwords_Block_View::_getJson()
     */
    public function getJson($params)
    {
        /** @var array $expected */
        $expected = $this->expected('json');

        /** @var string $expected JSON */
        $expected = $expected[0];

        /** @var Vaimo_PHPUnit_Helper_Data $testHelper */
        $testHelper = $this->_getTestHelper();

        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        $json = $testHelper->runMethod('_getJson', $block, array($params));

        $this->assertSame($expected, $json);

    }

    /**
     * Tests for Icommerce_Adwords_Block_View::getGoogleCustomTagParams()
     *
     * @test
     * @loadFixture
     * @loadExpectations
     * @see Icommerce_Adwords_Block_View::getGoogleCustomTagParams()
     */
    public function getGoogleCustomTagParams()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var array $expected */
        $expected = $this->expected('params');

        /** @var array $expected */
        $expected = $expected[0];

        $params = $block->getGoogleCustomTagParams();

        $this->assertSame($expected, $params);
    }

    /**
     * Tests for Icommerce_Adwords_Block_View::_getRemarketingModel()
     *
     * Not a real unit test but mocking static methods was removed in PHPUnit 4.
     * The tested method relies heavily on a call to a static factory method so
     * I am really doing a functional test here. (But what else is new when testing "unit" testing
     * Magento applications. There are very few true unit tests when testing Magento.)
     *
     * @test
     * @loadFixture
     * @see Icommerce_Adwords_Block_View::_getRemarketingModel()
     */
    public function getRemarketingModel()
    {
        /** @var Vaimo_PHPUnit_Helper_Data $testHelper */
        $testHelper = $this->_getTestHelper();

        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->_getBlock();

        /** @var Icommerce_Adwords_Model_Remarketing_Vertical_Retail $object */
        $object = $testHelper->runMethod('_getRemarketingModel', $block);

        $this->assertInstanceOf('Icommerce_Adwords_Model_Remarketing_Vertical_Retail', $object);
    }

    /**
     * Get the block we are testing
     *
     * @return Icommerce_Adwords_Block_View
     */
    protected function _getBlock()
    {
        /** @var Icommerce_Adwords_Block_View $block */
        $block = $this->getLayout()->createBlock(self::BLOCK_ALIAS);

        return $block;
    }

    /**
     * Get test helper
     *
     * @return Vaimo_PHPUnit_Helper_Data
     */
    protected function _getTestHelper()
    {
        if (!isset($this->_testHelper)) {
            /** @var Vaimo_PHPUnit_Helper_Data $helper */
            $helper = Mage::helper('vaimo_phpunit');

            $this->_testHelper = $helper;
        }

        return $this->_testHelper;
    }
}
