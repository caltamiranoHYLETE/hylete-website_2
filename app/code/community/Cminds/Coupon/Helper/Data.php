<?php
class Cminds_Coupon_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getErrorMessage($errorType, $rule = null)
    {
        $defaultMessage = 'Coupon code "%s" is not valid.';
        $defaultSuccessMessage = 'Coupon code "%s" is correct.';

        if($errorType == '') {
            return $this->escapeHtml($defaultMessage);
        }

        if(!empty($rule)){
            $unserializedErrors = unserialize($rule->getErrorsSerialized()) ? : array();
            $ruleErrors = $this->_findCurrentWebsiteError($unserializedErrors, Mage::app()->getStore()->getId());

            if(!$ruleErrors || !isset($ruleErrors[$errorType]) || empty($ruleErrors[$errorType])) {
                $ruleErrors = $this->_findCurrentWebsiteError($unserializedErrors, 0);
            }

            if(is_array($ruleErrors) && isset($ruleErrors[$errorType]) && !empty($ruleErrors[$errorType])
            ){
                return $this->escapeHtml($ruleErrors[$errorType]);
            }
        }

        $message = Mage::getStoreConfig('cminds_coupon/general/'.$errorType);
        if(empty($message)){
            if($errorType == 'coupon_code_is_correct'){
                $message = $defaultSuccessMessage;
            } else {
                $message = $defaultMessage;
            }
        }

        return $this->escapeHtml($message);
    }
    public function hasProductsMatchConditions(Varien_Object $object, $ruleConditions, $couponCode)
    {
        $all = $ruleConditions->getAggregator()==='all';

        $true = true;
        $found = false;
        $errorMsg = Mage::getStoreConfig('cminds_coupon/general/product_doesnot_apply_coupon');
        $message = $this->__($errorMsg, Mage::helper('core')->escapeHtml($couponCode));

        foreach ($object->getAllItems() as $item) {
            $found = $all;
            foreach ($ruleConditions->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if(!$validated) {
                    $errors[$item->getItemId()] = $message;
                }
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }

        if(isset($errors)) {
            Mage::getSingleton('core/session')->setCmindsCouponErrors($errors);
        }

        if ($found && $true) {
            return true;
        }
        elseif (!$found && !$true) {
            return true;
        } else {
            return false;
        }
    }

    public function matchProducts($rule, $cart, $couponCode) {
        if (!$rule->getActions()->getConditions()) {
            return true;
        }
        $true = true;
        $found = false;
        $errorMsg = Mage::getStoreConfig('cminds_coupon/general/product_doesnot_apply_coupon');
        $message = $this->__($errorMsg, Mage::helper('core')->escapeHtml($couponCode));

        $all    = $rule->getActions()->getAggregator() === 'all';
        $true   = (bool)$rule->getActions()->getValue();
        $errors = array();

        foreach($cart->getAllItems() AS $item) {
            foreach ($rule->getActions()->getConditions() as $cond) {
                $validated = $cond->validate($item);

                if(!$validated) {
                    $errors[$item->getItemId()] = $message;
                }

                if (($all && !$validated)) {
                    $found = $validated;
                    break;
                }
            }
        }

        if(isset($errors)) {
            Mage::getSingleton('core/session')->setCmindsCouponErrors($errors);
        }

        if ($found && $true) {
            return true;
        } elseif (!$found && !$true) {
            return true;
        } else {
            return false;
        }
    }

    private function _findCurrentWebsiteError($errors, $website) {

        foreach($errors AS $error) {
            if($error['store_id'] == $website) {
                return $error;
            }
        }

        return false;
    }
}
