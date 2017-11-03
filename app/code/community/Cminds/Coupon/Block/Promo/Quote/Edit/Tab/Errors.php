<?php

/**
 * Shopping Cart Price Rule Custom Coupons Error Messages Tab
 *
 * @author CreativeMindsSolutions
 */
class Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Errors
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('cminds_coupon')->__('Errors Messages');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cminds_coupon')->__('Errors Messages');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return (bool)Mage::getStoreConfig('cminds_coupon/general/module_enabled');
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_quote_rule');
        $modelData = $model->getData();

        $form = new Varien_Data_Form();

        if(isset($modelData['coupon_errors'])){
            $errorsValues = $modelData['coupon_errors'];
        }else{
            $s = unserialize($model->getErrorsSerialized());
            if(is_array($s)) {
                foreach($s as $data) {
                    if(isset($data['store_id']) && $data['store_id'] == 0) {
                        $errorsValues = $data;
                        break;
                    }
                }
            }
        }

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('cminds_coupon')->__('Define Individual Error Messages For Rule'))
        );

        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getId();

            $fieldset->addField('store_id', 'hidden', array(
                'name'     => 'coupon_errors[store_id]',
                'value'    => $websiteId
            ));
        } else {
            $values = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);

            $field = $fieldset->addField('store_id', 'select', array(
                'name' => 'coupon_errors[store_id]',
                'label' => $this->__('Store View'),
                'title' => $this->__('Store View'),
                'required' => true,
                'values' => $values,
            ));

            $stores = array();
            foreach (Mage::app()->getStores(true) as $store) {
                $stores[$store->getId()] = !is_null($store->getDefaultStore());
            }

            $field->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                var websites = " . Mage::helper('core')->jsonEncode($stores) .";
                var values = JSON.parse('".json_encode($s)."');
                Validation.add(
                    'validate-website-has-store',
                    '" . Mage::helper('customer')->__('Please select a website which contains store view') . "',
                    function(v, elem){
                        return websites[elem.value] == true;
                    }
                );
                Element.observe('store_id', 'change', function(){
                    Validation.validate($('store_id'))
                    var found = false;
                    for(i=0; i<=values.length; i++ ) {
                        if(values[i] && values[i].store_id == this.value) {
                            $('coupon_doesnt_apply_conditions').setValue(values[i].coupon_doesnt_apply_conditions);
                            $('coupon_code_is_expired').setValue(values[i].coupon_code_is_expired);
                            $('user_not_in_assigned_group').setValue(values[i].user_not_in_assigned_group);
                            $('over_used').setValue(values[i].over_used);
                            $('over_used_customer').setValue(values[i].over_used_customer);
                            $('coupon_code_is_correct').setValue(values[i].coupon_code_is_correct);
                            found = true;
                            break;
                        }
                    }

                    if(!found) {
                        $('coupon_doesnt_apply_conditions').setValue('');
                        $('coupon_code_is_expired').setValue('');
                        $('user_not_in_assigned_group').setValue('');
                        $('over_used').setValue('');
                        $('over_used_customer').setValue('');
                        $('coupon_code_is_correct').setValue('');
                    }
                }.bind($('store_id')));
                "
                . '</script>'
            );
        }


        $fieldset->addField('coupon_doesnt_apply_conditions', 'text', array(
            'name'  => 'coupon_errors[coupon_doesnt_apply_conditions]',
            'label' => Mage::helper('cminds_coupon')->__('Coupon doesn\'t apply conditions'),
            'title' => Mage::helper('cminds_coupon')->__('Coupon doesn\'t apply conditions'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['coupon_doesnt_apply_conditions'])) ? $errorsValues['coupon_doesnt_apply_conditions'] : ''
        ));

        $fieldset->addField('coupon_code_is_expired', 'text', array(
            'name'  => 'coupon_errors[coupon_code_is_expired]',
            'label' => Mage::helper('cminds_coupon')->__('Coupon is expired'),
            'title' => Mage::helper('cminds_coupon')->__('Coupon is expired'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['coupon_code_is_expired'])) ? $errorsValues['coupon_code_is_expired'] : ''
        ));

        $fieldset->addField('user_not_in_assigned_group', 'text', array(
            'name'  => 'coupon_errors[user_not_in_assigned_group]',
            'label' => Mage::helper('cminds_coupon')->__('Customer doesn\'t belong to the assigned customer group'),
            'title' => Mage::helper('cminds_coupon')->__('Customer doesn\'t belong to the assigned customer group'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['user_not_in_assigned_group'])) ? $errorsValues['user_not_in_assigned_group'] : ''
        ));

        $fieldset->addField('over_used', 'text', array(
            'name'  => 'coupon_errors[over_used]',
            'label' => Mage::helper('cminds_coupon')->__('Message when coupon was used more than it can be used'),
            'title' => Mage::helper('cminds_coupon')->__('Message when coupon was used more than it can be used'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['over_used'])) ? $errorsValues['over_used'] : ''
        ));

        $fieldset->addField('over_used_customer', 'text', array(
            'name'  => 'coupon_errors[over_used_customer]',
            'label' => Mage::helper('cminds_coupon')->__('Message when coupon was used more than it can be used in customer group'),
            'title' => Mage::helper('cminds_coupon')->__('Message when coupon was used more than it can be used in customer group'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['over_used_customer'])) ? $errorsValues['over_used_customer'] : ''
        ));

        $fieldset->addField('coupon_code_is_correct', 'text', array(
            'name'  => 'coupon_errors[coupon_code_is_correct]',
            'label' => Mage::helper('cminds_coupon')->__('Default success message'),
            'title' => Mage::helper('cminds_coupon')->__('Default success message'),
            'note'  => Mage::helper('cminds_coupon')->__('You can use the shortcode %s to display the coupon code used by the customer'),
            'value' => (isset($errorsValues['coupon_code_is_correct'])) ? $errorsValues['coupon_code_is_correct'] : ''
        ));

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_promo_quote_edit_tab_errors_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}
