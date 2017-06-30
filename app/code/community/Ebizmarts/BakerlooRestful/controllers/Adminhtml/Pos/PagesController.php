<?php

/**
 * POS static pages generator from admin panel instead of shell.
 */

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_PagesController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->_title($this->__('Static pages'))
            ->_title($this->__('POS'));

        $this->loadLayout();

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_notifications_grid')->toHtml()
        );
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('New Page'));
        $this->_title($this->__('Static pages'))
             ->_title($this->__('POS'));

        Mage::register('pos_staticpage', new Varien_Object);

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();
    }

    /**
     * Start pages generation process.
     */
    public function saveAction() {

        $page     = (int)$this->getRequest()->getParam('page', 1);

        $resource = $this->getRequest()->getParam('resource');
        $pageSize = (int)$this->getRequest()->getParam('size', 400);
        $storeId  = (int)$this->getRequest()->getParam('store_id');

        //Avoid flat catalog on static files generation
        Mage::app()->setCurrentStore($storeId);

        Mage::helper('bakerloo_restful/pages')->disableFlatCatalogAndCategory($storeId);

        try {
            $data = Mage::helper('bakerloo_restful/pages')->getData($resource, -1, $pageSize, $page, $storeId);
        } catch(Exception $ex) {
            return $this->getResponse()->setBody("Generation failed! \n" . $ex->getMessage());
        }

        //Store page 1
        Mage::helper('bakerloo_restful/pages')->storeData($resource, $data, $storeId, $page);

        $response = array(
            'total_records' => $data['total_count'],
            'total_pages'   => $data['total_pages'],
        );

        return $this->getResponse()->setBody(Zend_Json::encode($response));
    }

    public function treeAction() {

        $node = $this->getRequest()->getPost('node', 'root');

        $data = array();

        $baseDir = Mage::getBaseDir('var') . DS . 'pos';

        if($node != 'root') {
            $baseDir .= $node;
        }

        try {
            $iterator = new DirectoryIterator($baseDir);
            foreach ($iterator as $fileInfo) {

                $fileName = $fileInfo->getFilename();
                if($fileInfo->isDot() or '.DS_Store' == $fileName) {
                    continue;
                }

                $name = str_replace('.ser', '', $fileName);

                if((int)$name > 0)
                    $name = $this->_getStoreNameFull($name);

                if(!$fileInfo->isDir())
                    $name .= sprintf(' (%s) Created on %s', $this->_formattedSize($fileInfo->getSize()), date('Y-m-d H:i:s', $fileInfo->getCTime()));

                $data []= array (
                    'text' => $name,
                    'id'   => str_replace(Mage::getBaseDir('var') . DS . 'pos', '', $fileInfo->getPath()) . DS . $fileInfo->getFilename(),
                    'cls'  => $fileInfo->isDir() ? 'folder' : 'file',
                    'leaf' => !$fileInfo->isDir(),
                );

            }

            usort($data, array($this, "sort_tree"));

        }catch(Exception $ex) {
            //Directory is empty or something.
            Mage::logException($ex);
        }

        $this->getResponse()->setBody(Zend_Json::encode($data));
    }

    private function _formattedSize($fileSize){
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $unit = $fileSize > 0 ? intval(log($fileSize, 1024)) : 0;
        $size = $fileSize / pow(1024, $unit);

        return sprintf('%d %s', $size, $units[$unit]);
    }

    protected function _getStoreNameFull($storeId) {
        $store = Mage::app()->getStore($storeId);

        $ret = '';

        $ret .= $store->getWebsite()->getName();
        $ret .= ' > ';
        $ret .= $store->getFrontendName();
        $ret .= ' > ';
        $ret .= $store->getName();

        return $ret;
    }

    public function sort_tree($a, $b) {
        return strcasecmp($a['text'], $b['text']);
    }

    public function cacheAction() {

        $resourceInfo = $this->getRequest()->getParam('resource');
        $resourceInfo = explode('_', $resourceInfo);

        $resourceName = $resourceInfo[0];
        $cacheType    = $resourceInfo[1];

        $status = ucfirst($resourceName) . ' - ' . strtoupper($cacheType) . ': ';

        try {

            if($cacheType == 'zip') {
                Mage::helper('bakerloo_restful/pages')->getZippedPages($this->getRequest(), $this->getResponse(), $resourceName);
            }
            else {
                if($cacheType == 'db') {
                    Mage::helper('bakerloo_restful/pages')->getDB($this->getRequest(), $this->getResponse(), true, $resourceName);
                }
            }

            //$this->getResponse()->setBody($status . ' OK.');

        }catch (Exception $ex) {
            $this->getResponse()->setBody($status . ' ERROR.');
        }
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'edit':
            case 'cache':
            case 'save':
                $acl = 'ebizmarts_pos/pages/new_edit';
                break;
            case 'tree':
            case 'index':
            case 'grid':
                $acl = 'ebizmarts_pos/pages/list';
                break;
            default:
                $acl = 'ebizmarts_pos/pages/list';
        }

        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

}