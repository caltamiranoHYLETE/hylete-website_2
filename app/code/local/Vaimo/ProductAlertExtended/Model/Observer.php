<?php

/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @package     Vaimo_ProductAlertExtended
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Giorgos Tsioutsiouliklis <giorgos@vaimo.com>
 */
class Vaimo_ProductAlertExtended_Model_Observer extends Mage_ProductAlert_Model_Observer
{
    protected $_successCount = 0;
    protected $_failureCount = 0;

    protected $_failureMessages = array();

    public function getSuccessCount()
    {
        return $this->_successCount;
    }

    public function getFailureCount()
    {
        return $this->_failureCount;
    }

    public function getFailureMessages()
    {
        return implode(', ', $this->_failureMessages);
    }

    public function addFailureMessage($message)
    {
        array_push($this->_failureMessages, $message);
    }

    public function getUseQtyInBAckToStockCheck()
    {
        return Mage::helper('productalertextended')->getSettingFromProductAlert(Vaimo_ProductAlertExtended_Helper_Data::PARAM_QTY_IN_NOTIFY_CHECK);
    }

    /**
     * Process stock emails
     *
     * @param Mage_ProductAlert_Model_Email $email
     * @return Mage_ProductAlert_Model_Observer
     */
    protected function _processStock(Vaimo_ProductAlertExtended_Model_Email $email)
    {
        $useQtyInBackToStockCheck = $this->getUseQtyInBAckToStockCheck();

        $email->setType('stock');

        foreach ($this->_getWebsites() as $website) {
            /* @var $website Mage_Core_Model_Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()
                            ->getDefaultStore()
            ) {
                continue;
            }
            if (!Mage::getStoreConfig(self::XML_PATH_STOCK_ALLOW, $website->getDefaultGroup()
                            ->getDefaultStore()
                            ->getId())
            ) {
                continue;
            }
            try {
                $collection = Mage::getModel('productalertextended/stock')
                        ->getCollection()
                        ->addWebsiteFilter($website->getId())
                        ->addStatusFilter(0)
                        ->setCustomerOrder();
            } catch (Exception $e) {
                $this->_errors [] = $e->getMessage();
                return $this;
            }

            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                try {
                    if (!$previousCustomer || $previousCustomer->getId() != $alert->getCustomerId()) {
                        if (( bool )$alert->getCustomerId()) {
                            $customer = Mage::getModel('customer/customer')
                                    ->load($alert->getCustomerId());
                        } else { // Special not registered case
                            $customer = Mage::getModel('customer/customer');
                            $customer->setEmail($alert->getEmail());
                            $customer->setId('0');
                            $customer->setName('');
                        }

                        if ($previousCustomer) {
                            $email->send();
                        }
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomer($customer);
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product = Mage::getModel('catalog/product')
                            ->setStoreId($website->getDefaultStore()
                                            ->getId())
                            ->load($alert->getProductId());
                    /* @var $product Mage_Catalog_Model_Product */
                    if (!$product) {
                        continue;
                    }

                    $product->setCustomerGroupId($customer->getGroupId());

                    if ($product->isSalable() && $this->checkQty($useQtyInBackToStockCheck, $product)) {
                        $email->addStockProduct($product);

                        $alert->setSendDate(Mage::getModel('core/date')
                                        ->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();

                        $this->_successCount++;
                    }
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();

                    $this->addFailureMessage($errorMessage);
                    $this->_failureCount++;

                    $this->_errors [] = $errorMessage;
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (Exception $e) {
                    $this->_errors [] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    protected function checkQty($shouldCheckQty, $product){
        if (!$shouldCheckQty){
            return true;
        }elseif($shouldCheckQty && $product->getStockItem()->getQty() > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Run process send product alerts
     *
     * @return Mage_ProductAlert_Model_Observer
     */
    public function process()
    {
        $email = Mage::getModel('productalertextended/email');
        /* @var $email Mage_ProductAlert_Model_Email */
        $this->_processPrice($email);
        $this->_processStock($email);
        $this->_sendErrorEmail();

        return $this;
    }
}
