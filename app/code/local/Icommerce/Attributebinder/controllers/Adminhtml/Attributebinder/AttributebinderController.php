<?php

class Icommerce_Attributebinder_Adminhtml_Attributebinder_AttributebinderController extends Mage_Adminhtml_Controller_action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/attributes');
    }

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('catalog/attributebinder')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Attributebinder Manager'), Mage::helper('adminhtml')->__('Attributebinder Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('attributebinder/adminhtml_attributebinder'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('attributebinder/attributebinder');
        $model->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('attributebinder_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('attributebinder/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Attributebinder Manager'), Mage::helper('adminhtml')->__('Attributebinder Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Attributebinder Edit'), Mage::helper('adminhtml')->__('Attributebinder Edit'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('attributebinder/adminhtml_attributebinder_edit'))
                    ->_addLeft($this->getLayout()->createBlock('attributebinder/adminhtml_attributebinder_edit_tabs'));
            $this->renderLayout();

        } else {

            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('attributebinder')->__('Binding does not exist'));
            $this->_redirect('*/*/');

        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            //$data['model_image'] = self::fileUpload($_FILES['model_image']['name']);

            $model = Mage::getModel('attributebinder/attributebinder');

            $model->setMainAttributeId($this->getRequest()->getParam('main_attribute_id'));
            $model->setBindAttributeId($this->getRequest()->getParam('bind_attribute_id'));
            $model->setDefaultMainAttribute($this->getRequest()->getParam('default_main_attribute'));

            $main_attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $this->getRequest()->getParam('main_attribute_id'));
            $model->setMainAttributeCode($main_attribute->getAttributeCode());
            $model->setMainAttributeLabel($main_attribute->getFrontendLabel());
            $bind_attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $this->getRequest()->getParam('bind_attribute_id'));
            $model->setBindAttributeCode($bind_attribute->getAttributeCode());
            $model->setBindAttributeLabel($bind_attribute->getFrontendLabel());

            $model->setSuppressManMainAttr($this->getRequest()->getParam('suppress_man_main_attr'));

            $new = true;
            if($this->getRequest()->getParam('id') != ""){
                $model->setId($this->getRequest()->getParam('id'));
                $new = false;
            }


            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $model->save();
                if($new){
                    $bind_attribute = $this->getRequest()->getParam('bind_attribute_id');
                    $values = $model->getAttributeValues($bind_attribute);
                    $model->saveDefaultBindingValues($values);
                }else{
                    //
                    $main_attributes = $this->getRequest()->getParam('main_attribute_value');
                    $bind_attributes = $this->getRequest()->getParam('bind_attribute_value');

                    $model->saveBindingValues($main_attributes,$bind_attributes);

                }

                if($new){
                    Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('attributebinder')->__('Bindings was successfully saved, add bindings'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                }else{
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('attributebinder')->__('Bindings was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);
                }

                if ($new || $this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }


                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('attributebinder')->__('Unable to find binding to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('attributebinder/attributebinder');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('attributebinder')->__('Binding was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $lookbookIds = $this->getRequest()->getParam('bindings');
        if (!is_array($lookbookIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('attributebinder')->__('Please select binding(s)'));
        } else {
            try {
                foreach ($lookbookIds as $lookbookId) {
                    $lookbook = Mage::getModel('attributebinder/attributebinder')->load($lookbookId);
                    $lookbook->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('attributebinder')->__(
                        'Total of %d binding(s) were successfully deleted', count($lookbookIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    public function reindexAction(){
        $id_str = implode( ',', $_POST["attributebinder"] );
        $attrs = Icommerce_Db::getRows( "SELECT bind_attribute_code, main_attribute_code, suppress_man_main_attr FROM icommerce_attributebinder
                                                     WHERE id IN ($id_str)" );

        try {
            // Reindex all products, that is, set the "main" values whenever we have set the "bind" value on the product
            $lut = array();
            $hlp = Mage::helper( "attributebinder" );
            $chg_cnt = 0;
            foreach( Icommerce_Products::getAllIds(true) as $pid ){
                if( $pid==4996 ){
                    $x = 1;
                }
                foreach( $attrs as $row ){
                    $acode_main = $row["main_attribute_code"];
                    $acode_bind = $row["bind_attribute_code"];
                    if( $v = Icommerce_Eav::getValue( $pid, $acode_bind, "catalog_product" ) ){
                        if( !isset($lut[$acode_bind][$acode_main][$v]) ){
                            $v_val = Icommerce_Default::getOptionValue( $v, $acode_bind );
                            $curr_main_attr_val = Icommerce_Eav::getValue( $pid, $acode_main, "catalog_product" );
                            if ($curr_main_attr_val && $row['suppress_man_main_attr'] == 1){
                                continue;
                            }
                            if( $v_val=="46100" ){
                                $x = 1;
                            }
                            $v_main_val = $hlp->lookupBoundValueByMain( $acode_main, $v_val );
                            if(is_array($v_main_val)) {
                                $v_main_opt = array();
                                foreach($v_main_val as $v_main_value) {
                                    $v_main_opt[] = Icommerce_Default::getOptionValueId($acode_main, $v_main_value);
                                }
                            } else {
                                $v_main_opt = Icommerce_Default::getOptionValueId($acode_main, $v_main_val);
                            }
                            $lut[$acode_bind][$acode_main][$v] = $v_main_opt;
                        }
                    }
                    $v_main = $v ? $lut[$acode_bind][$acode_main][$v] : "";
                    if(is_array($v_main)) {
                        $v_main = implode(",", $v_main);
                    }
                    if( $v_main!=Icommerce_Eav::getValue($pid,$acode_main,"catalog_product") ){
                        Icommerce_Eav::setValue($pid,$acode_main,$v_main,"catalog_product");
                        $chg_cnt++;
                    }
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('attributebinder')->__(
                    'Total of %d main attribute values were successfully updated', $chg_cnt
                ) );
        } catch( Exception $e ){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

}
