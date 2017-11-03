<?php
class Cminds_Coupon_Model_Adminhtml_Observer
{
    public function onSalesrulePrepareSave($observer)
    {
        if(Mage::getStoreConfig('cminds_coupon/general/module_enabled')){
            $request = $observer->getRequest();
            $couponErrorsArr = $request->getPost('coupon_errors');

            $rule = Mage::getModel('salesrule/rule')->load($request->getPost('rule_id'));

            if($rule) {
                $unserializedData = $rule->getErrorsSerialized();
                $found = false;

                if($unserializedData) {
                    $unserializedData = unserialize($unserializedData);
                    foreach($unserializedData AS $i => $data) {
                        if($couponErrorsArr['store_id'] == $data['store_id']) {
                            $found = true;
                            $unserializedData[$i] = $couponErrorsArr;
                            break;
                        }
                    }
                    if($found) {
                        $couponErrorsArr = $unserializedData;
                    } else {
                        $unserializedData[] = $couponErrorsArr;
                        $couponErrorsArr = array();
                        $couponErrorsArr = $unserializedData;
                    }
                } else {
                    $a = $couponErrorsArr;
                    $couponErrorsArr = array();
                    $couponErrorsArr[] = $a;

                }
            }
            $request->setPost('errors_serialized',serialize($couponErrorsArr));
        }
    }
}