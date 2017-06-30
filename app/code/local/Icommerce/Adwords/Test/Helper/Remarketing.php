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
 * Class Icommerce_Adwords_Test_Helper_Remarketing
 *
 * Tests for Icommerce_Adwords_Helper_Remarketing. This class extends controller test
 * case because we need to do several assertions after requests has been dispatched.
 *
 * @see Icommerce_Adwords_Helper_Remarketing
 * @group Icommerce_Adwords
 */
class Icommerce_Adwords_Test_Helper_Remarketing extends EcomDev_PHPUnit_Test_Case_Controller
{

    /** Magento helper alias */
    const HELPER_ALIAS = 'adwords/remarketing';

    /**
     * Clean up after every test
     */
    public function tearDown()
    {
        // Reset request
        $this->reset();
    }

    /**
     * Tests for Icommerce_Adwords_Helper_Remarketing::getCurrentControllerName()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getCurrentControllerName()
     */
    public function getCurrentControllerName()
    {
        // Dispatching some random route
        $this->dispatch('home/cms');

        /** @var string $controllerName */
        $controllerName = $this->getRequest()->getControllerName();

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertSame($controllerName, $helper->getCurrentControllerName());
    }

    /**
     * Tests for Icommerce_Adwords_Helper_Remarketing::getCurrentActionName()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getCurrentActionName()
     */
    public function getCurrentActionName()
    {
        // Dispatching some random route
        $this->dispatch('home/cms');

        /** @var string $actionName */
        $actionName = $this->getRequest()->getActionName();

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertSame($actionName, $helper->getCurrentActionName());
    }

    /**
     * Tests for Icommerce_Adwords_Helper_Remarketing::onSearchResultPage()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::onSearchResultPage()
     */
    public function onSearchResultPage()
    {
        $this->_assertOnAdvancedSearchResultPage();
        $this->_assertOnCatalogsearchResultPage();
        $this->_assertNotOnSearchResultPage();

        // Special test for Klevu_Search
        if (Mage::helper('core')->isModuleEnabled('Klevu_Search')) {
            $this->_assertOnKlevuResultPage();
        }
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::onProductPage()
     *
     * @test
     * @loadFixture ~Vaimo_PHPUnit/catalog
     * @see Icommerce_Adwords_Helper_Remarketing::onProductPage()
     */
    public function onProductPage()
    {
        $this->dispatch('catalog/product/view/id/10');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onProductPage());

        Mage::unregister('current_product');

        $this->_assertNotOnProductPage();
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::onCategoryPage()
     *
     * @test
     * @loadFixture ~Vaimo_PHPUnit/catalog
     * @registry current_category
     * @see Icommerce_Adwords_Helper_Remarketing::onCategoryPage()
     */
    public function onCategoryPage()
    {
        /*
         * This ampersand is a horrible fix.
         * It is added because Icommerce_MultiOptionFilter_CategoryController starts
         * session by using "setcookie". That will break because the PHPUnit test suite
         * starts output per default.
         */
        @$this->dispatch('catalog/category/view/id/3');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onCategoryPage());

        $this->_assertNotOnCategoryPage();

    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::onHomePage()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::onHomePage()
     */
    public function onHomePage()
    {
        $this->dispatch('/');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onHomePage());

        $this->_assertNotOnHomePage();
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::onCartPage()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::onCartPage()
     */
    public function onCartPage()
    {
        $this->dispatch('checkout/cart');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onCartPage());

        $this->_assertNotOnCartPage();
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::onCheckoutPage()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::onCheckoutPage()
     */
    public function onCheckoutPage()
    {
        $this->dispatch('checkout/onepage');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onCheckoutPage());

        $this->_assertNotOnCheckoutPage();
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::getQuote()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getQuote()
     */
    public function getQuote()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertInstanceOf('Mage_Sales_Model_Quote', $helper->getQuote());
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::getCartHelper()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getCartHelper()
     */
    public function getCartHelper()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertInstanceOf('Mage_Checkout_Helper_Cart', $helper->getCartHelper());
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::getCurrentCategory()
     *
     * @loadFixture ~Vaimo_PHPUnit/catalog
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getCurrentCategory()
     */
    public function getCurrentCategory()
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = Mage::getModel('catalog/category')->load(2);

        Mage::register('current_category', $category);

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $categoryFromHelper = $helper->getCurrentCategory();

        $this->assertSame($category->getId(), $categoryFromHelper->getId());

        Mage::unregister('current_category');
    }

    /**
     * Test for Icommerce_Adwords_Helper_Remarketing::getCatalogLayer()
     *
     * @test
     * @see Icommerce_Adwords_Helper_Remarketing::getCatalogLayer()
     */
    public function getCatalogLayer()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertInstanceOf('Mage_Catalog_Model_Layer', $helper->getCatalogLayer());
    }

    /**
     * Assert that we are not on cart page
     */
    protected function _assertNotOnCheckoutPage()
    {
        $this->reset();

        // Dispatching some random route
        $this->dispatch('/');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onCheckoutPage());
    }

    /**
     * Assert that we are not on cart page
     */
    protected function _assertNotOnCartPage()
    {
        $this->reset();

        // Dispatching some random route
        $this->dispatch('/');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onCartPage());
    }

    /**
     * Assert that we are not on home page
     */
    protected function _assertNotOnHomePage()
    {
        $this->reset();

        // Dispatching some random route
        $this->dispatch('random/url');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onHomePage());
    }

    /**
     * Assert that we are not on a category page
     */
    protected function _assertNotOnCategoryPage()
    {
        $this->reset();

        Mage::unregister('current_category');

        // Dispatching some random route
        $this->dispatch('home/cms');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onCategoryPage());
    }

    /**
     * Assert that we are not on a product page
     */
    protected function _assertNotOnProductPage()
    {
        $this->reset();

        // Dispatching some random route
        $this->dispatch('home/cms');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onProductPage());
    }

    /**
     * Assert that we are not on a search result page
     */
    protected function _assertNotOnSearchResultPage()
    {
        $this->reset();

        // Dispatching some random route
        $this->dispatch('home/cms');

        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertFalse($helper->onSearchResultPage());
    }

    /**
     * Assert that we are on the advanced search results page
     */
    protected function _assertOnAdvancedSearchResultPage()
    {
        $this->reset();

        $this->getRequest()->setQuery(array('name' => 'productname'));

        // Dispatching some random route
        $this->dispatch('catalogsearch/advanced/result');


        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onSearchResultPage());
    }

    /**
     * Assert that we are on the regular search results page
     */
    protected function _assertOnCatalogsearchResultPage()
    {
        $this->reset();

        $this->getRequest()->setQuery(array('q' => 'productname'));

        // Dispatching some random route
        $this->dispatch('catalogsearch/result/index');


        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onSearchResultPage());
    }

    /**
     * Assert that we are on the Klevu search results page
     */
    protected function _assertOnKlevuResultPage()
    {
        $this->reset();

        $this->getRequest()->setQuery(array('q' => 'productname'));

        // Dispatching some random route
        $this->dispatch('search/index/index');


        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = $this->_getHelper();

        $this->assertTrue($helper->onSearchResultPage());
    }

    /**
     * Get the helper we are testing
     *
     * @return Icommerce_Adwords_Helper_Remarketing
     */
    protected function _getHelper()
    {
        /** @var Icommerce_Adwords_Helper_Remarketing $helper */
        $helper = Mage::helper(self::HELPER_ALIAS);

        return $helper;
    }
}
