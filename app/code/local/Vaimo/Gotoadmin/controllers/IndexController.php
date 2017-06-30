<?php
class Vaimo_Gotoadmin_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $_publicActions = array('editcmsblock', 'editcategory', 'editcms', 'productlist', 'systemconfiguration');
    
    /**
     * Name of "is URLs checked" flag
     */
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'adminhtml';

    /**
     * Used module name in current adminhtml controller
     */
    protected $_usedModuleName = 'adminhtml';

    /**
     * Currently used area
     *
     * @var string
     */
    protected $_currentArea = 'adminhtml';

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        // override admin store design settings via stores section
        Mage::getDesign()
            ->setArea($this->_currentArea)
            ->setPackageName((string)Mage::getConfig()->getNode('stores/admin/design/package/name'))
            ->setTheme((string)Mage::getConfig()->getNode('stores/admin/design/theme/default'))
        ;
        foreach (array('layout', 'template', 'skin', 'locale') as $type) {
            if ($value = (string)Mage::getConfig()->getNode("stores/admin/design/theme/{$type}")) {
                Mage::getDesign()->setTheme($type, $value);
            }
        }

        $this->getLayout()->setArea($this->_currentArea);

        Mage::dispatchEvent('adminhtml_controller_action_predispatch_start', array());
        parent::preDispatch();
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_validateFormKey();
                $_keyErrorMsg = Mage::helper('adminhtml')->__('Invalid Form Key. Please refresh the page.');
            } elseif (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = Mage::helper('adminhtml')->__('Invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'error' => true,
                    'message' => $_keyErrorMsg
                )));
            } else {
                $this->_redirect( Mage::getSingleton('admin/session')->getUser()->getStartupPageUrl() );
            }
            return $this;
        }

        if ($this->getRequest()->isDispatched()
            && $this->getRequest()->getActionName() !== 'denied'
            && !$this->_isAllowed()) {
            $this->_forward('denied');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if (!$this->getFlag('', self::FLAG_IS_URLS_CHECKED)
            && !$this->getRequest()->getParam('forwarded')
            && !$this->_getSession()->getIsUrlNotice(true)
            && !Mage::getConfig()->getNode('global/can_use_base_url')) {
            //$this->_checkUrlSettings();
            $this->setFlag('', self::FLAG_IS_URLS_CHECKED, true);
        }
        if (is_null(Mage::getSingleton('adminhtml/session')->getLocale())) {
            Mage::getSingleton('adminhtml/session')->setLocale(Mage::app()->getLocale()->getLocaleCode());
        }

        return $this;
    }

    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        if (is_array($this->_publicActions) && in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return true;
        }

        if (!($secretKey = $this->getRequest()->getParam(Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME, null))
            || $secretKey != Mage::getSingleton('adminhtml/url')->getSecretKey()) {
            return false;
        }
        return true;
    }
    
    
    
    
    
    public function editcmsblockAction()
    {
        $identifier = $this->getRequest()->getParam('identifier');
        if ($identifier) {
            $block = Mage::getModel('cms/block')->load($identifier);
            if ($block) {
                $block_id = $block->getBlockId();
                Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/cms_block/edit", array('block_id' => $block_id)));
            }
        }
    }
	
    public function productlistAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product/index"));
    }
    
    public function systemconfigurationAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/dev'));
    }
    
    public function editcmsAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/cms_page/edit", array('page_id' => $this->getRequest()->getParam('page_id'))));
    }
    
    public function categorylistAction()
    {
        Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/catalog_category"));
    }
    
    public function editcategoryAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (ctype_digit($id)) {
            $category = Mage::getModel('catalog/category')->load($id);
            if ($category) {
                $path = $category->getPath();
                
                Mage::getSingleton('core/session')->setGotoadminCategoryId($id);
                Mage::getSingleton('core/session')->setGotoadminCategoryPath($path);
                
                Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("adminhtml/catalog_category"));
            }
        }
    }
}