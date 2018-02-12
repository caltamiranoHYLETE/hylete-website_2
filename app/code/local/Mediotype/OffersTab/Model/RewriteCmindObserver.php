<?php

/**
 * Class Mediotype_OffersTab_Model_RewriteCmindObserver
 */
class Mediotype_OffersTab_Model_RewriteCmindObserver extends Cminds_Coupon_Model_Observer
{
    /**
     * @param $observer
     * @return array|void
     */
    public function checkCoupon($observer)
    {
        $controllerObject = $observer->getControllerAction();
        $couponCode = (string)$controllerObject->getRequest()->getPost('coupon_code', '');

        $quote = Mage::helper('checkout/cart')->getQuote();
        if ($controllerObject->getRequest()->getParam('remove') == '1') {
            $couponCode = '';
        }

        $oldCouponCode = $quote->getCouponCode();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            return;
        }

        Mage::dispatchEvent("mediotype_coupon_removal_check", ['quote' => $quote, 'couponCodeRemoved' => $oldCouponCode]);

        try {
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $quote->getCouponCode()) {
                    $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                    $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

                    if ($coupon->getId()) {
                        $error = false;
                        $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_code_is_correct', $rule), Mage::helper('core')->htmlEscape($couponCode));
                    }
                } else {
                    $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
                    $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

                    if (Mage::getStoreConfig('cminds_coupon/general/module_enabled')) {
                        $errorCount = Mage::getModel('cminds_coupon/error')->load($coupon->getId(), 'coupon_id');
                        $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

                        if (!$errorCount->getId()) {
                            $errorCount->setCouponId($coupon->getId());
                            $errorCount->setRuleId($coupon->getRuleId());
                            $errorCount->save();
                        }
                        $existsError = false;

                        if (!$coupon->getId()) {

                            $error = true;
                            $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_doesnt_exists', $rule), Mage::helper('core')->htmlEscape($couponCode));

                            $existsError = true;
                        }

                        if (!$existsError) {
                            if (!$rule->getIsActive()) {

                                $error = true;
                                $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_doesnt_exists', $rule), Mage::helper('core')->htmlEscape($couponCode));
                                $log = Mage::getModel('cminds_coupon/log');
                                $log->setCreatedOn(date('Y-m-d H:i:s'));
                                $log->setCouponId($coupon->getId());
                                $log->setErrorType(4);
                                $log->save();

                                $errorCount->setExpired($errorCount->getExpired() + 1);
                                $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                                $existsError = true;
                            }
                        }


                        if (!$existsError) {
                            if ($coupon->getExpirationDate() != NULL) {
                                $expirationDate = new DateTime($coupon->getExpirationDate());
                                $now = new DateTime();

                                if ($now > $expirationDate) {

                                    $error = true;
                                    $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_code_is_expired', $rule), Mage::helper('core')->htmlEscape($couponCode));

                                    $log = Mage::getModel('cminds_coupon/log');
                                    $log->setCreatedOn(date('Y-m-d H:i:s'));
                                    $log->setCouponId($coupon->getId());
                                    $log->setErrorType(1);
                                    $log->save();

                                    $errorCount->setExpired($errorCount->getExpired() + 1);
                                    $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                                    $existsError = true;
                                }
                            }
                        }

                        if (!$existsError && $coupon->getUsageLimit() && $coupon->getTimesUsed() + 1 > $coupon->getUsageLimit()) {

                            $error = true;
                            $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('over_used', $rule), Mage::helper('core')->htmlEscape($couponCode));

                            $log = Mage::getModel('cminds_coupon/log');
                            $log->setCreatedOn(date('Y-m-d H:i:s'));
                            $log->setCouponId($coupon->getId());
                            $log->setErrorType(5);
                            $log->save();

                            $errorCount->setOverUsed($errorCount->getOverUsed() + 1);
                            $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                            $existsError = true;
                        }

                        if (!$existsError && $customerGroupId && $coupon->getUsagePerCustomer()) {
                            $couponUsage = new Varien_Object();
                            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                            Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon($couponUsage, $customerId, $coupon->getId());

                            if ($couponUsage->getCouponId() && $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()) {

                                $error = true;
                                $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('over_used_customer', $rule), Mage::helper('core')->htmlEscape($couponCode));

                                $log = Mage::getModel('cminds_coupon/log');
                                $log->setCreatedOn(date('Y-m-d H:i:s'));
                                $log->setCouponId($coupon->getId());
                                $log->setErrorType(6);
                                $log->save();

                                $errorCount->setOverUsedByCustomer($errorCount->getOverUsedByCustomer() + 1);
                                $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                                $existsError = true;
                            }
                        }

                        if (!$existsError) {
                            if (!in_array($customerGroupId, $rule['customer_group_ids'])) {

                                $error = true;
                                $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('user_not_in_assigned_group', $rule), Mage::helper('core')->htmlEscape($couponCode));

                                $log = Mage::getModel('cminds_coupon/log');
                                $log->setCreatedOn(date('Y-m-d H:i:s'));
                                $log->setCouponId($coupon->getId());
                                $log->setErrorType(2);
                                $log->save();

                                $errorCount->setCustomerNotAssigned($errorCount->getCustomerNotAssigned() + 1);
                                $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                                $existsError = true;
                            }
                        }

                        if (!$existsError) {
                            $matchConditions = Mage::helper('cminds_coupon')->hasProductsMatchConditions($quote, $rule->getConditions(), $couponCode);

                            if (!$matchConditions) {
                                $error = true;
                                $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_doesnt_apply_conditions', $rule), Mage::helper('core')->htmlEscape($couponCode));


                                $log = Mage::getModel('cminds_coupon/log');
                                $log->setCreatedOn(date('Y-m-d H:i:s'));
                                $log->setCouponId($coupon->getId());
                                $log->setErrorType(3);
                                $log->save();

                                $errorCount->setDoesntMatchConditions($errorCount->getDoesntMatchConditions() + 1);
                                $errorCount->setLastOccured(date('Y-m-d H:i:s'));

                                $existsError = true;
                            }
                        }

                        if (!$existsError) {
                            $matchConditions = Mage::helper('cminds_coupon')->matchProducts($rule, $quote, $couponCode);

                            if (!$matchConditions) {
                                $log = Mage::getModel('cminds_coupon/log');
                                $log->setCreatedOn(date('Y-m-d H:i:s'));
                                $log->setCouponId($coupon->getId());
                                $log->setErrorType(3);
                                $log->save();

                                $errorCount->setDoesntMatchConditions($errorCount->getDoesntMatchConditions() + 1);

                                $error = true;
                                $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('coupon_doesnt_apply_conditions', $rule), Mage::helper('core')->escapeHtml($couponCode));

                                $existsError = true;
                            }
                        }

                        if (!$existsError) {

                            $error = true;
                            $message = Mage::helper('core')->__(Mage::helper('cminds_coupon')->getErrorMessage('default_error_message', $rule), Mage::helper('core')->htmlEscape($couponCode));

                            $log = Mage::getModel('cminds_coupon/log');
                            $log->setCreatedOn(date('Y-m-d H:i:s'));
                            $log->setCouponId($coupon->getId());
                            $log->setErrorType(4);
                            $log->save();

                            $errorCount->setDefaultError($errorCount->getDefaultError() + 1);
                            $errorCount->setLastOccured(date('Y-m-d H:i:s'));
                        }

                        $errorCount->save();

                    } else {
                        $error = true;
                        $message = Mage::helper('core')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                    }
                }
            } else {
                $error = true;
                $message = Mage::helper('core')->__('Coupon code was canceled.');
            }

        } catch (Mage_Core_Exception $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (Exception $e) {
            $error = true;
            $message = Mage::helper('core')->__('Cannot apply the coupon code.');
            Mage::logException($e);
        }
        $result = array(
            'error' => $error,
            'message' => $message
        );

        return $result;
    }
}
