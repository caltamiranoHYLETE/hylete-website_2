<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 * @comment     This is an exact copy of the Mage_Core_Model_Factory. Copied to allow backwards compatibility with older Magento versions and allow more flexible testing.
 */

class Vaimo_Cms_Model_Core_Factory
{
    /**
     * Xml path to url rewrite model class alias
     */
    const XML_PATH_URL_REWRITE_MODEL = 'global/url_rewrite/model';

    const XML_PATH_INDEX_INDEX_MODEL = 'global/index/index_model';

    /**
     * Config instance
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Initialize factory
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->_config = !empty($arguments['config']) ? $arguments['config'] : Mage::getConfig();
    }

    /**
     * Retrieve model object
     *
     * @param string $modelClass
     * @param array|object $arguments
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getModel($modelClass = '', $arguments = array())
    {
        return Mage::getModel($modelClass, $arguments);
    }

    /**
     * Retrieve model object singleton
     *
     * @param string $modelClass
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     */
    public function getSingleton($modelClass = '', array $arguments = array())
    {
        return Mage::getSingleton($modelClass, $arguments);
    }

    /**
     * Retrieve object of resource model
     *
     * @param string $modelClass
     * @param array $arguments
     * @return Object
     */
    public function getResourceModel($modelClass, $arguments = array())
    {
        return Mage::getResourceModel($modelClass, $arguments);
    }

    /**
     * Retrieve helper instance
     *
     * @param string $helperClass
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper($helperClass)
    {
        return Mage::helper($helperClass);
    }

    /**
     * Get config instance
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Retrieve url_rewrite instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewriteInstance()
    {
        return $this->getModel($this->getUrlRewriteClassAlias());
    }

    /**
     * Retrieve alias for url_rewrite model
     *
     * @return string
     */
    public function getUrlRewriteClassAlias()
    {
        return (string)$this->_config->getNode(self::XML_PATH_URL_REWRITE_MODEL);
    }

    /**
     * @return string
     */
    public function getIndexClassAlias()
    {
        return (string)$this->_config->getNode(self::XML_PATH_INDEX_INDEX_MODEL);
    }
}
