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
 * @package     Vaimo_EtonIntegration
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBaseStandard_Model_Export_Order extends Vaimo_IntegrationBaseStandard_Model_Export_Abstract
{
    const CONFIG_XML_PATH_ZERO_FIELDS = 'integrationbase/standard/export_zero_fields';
    const CONFIG_XML_PATH_BLANK_FIELDS = 'integrationbase/standard/export_blank_fields';
    const CONFIG_XML_LINE_ID_SEPARATE = 'integrationbase/standard/export_line_id_separate';

    private $_xml_writer = NULL;
    
    protected function _getXmlWriter()
    {
        if (!$this->xml_writer) {
            $this->xml_writer = Mage::getModel('integrationbasestandard/xml_export');
        }
        return $this->xml_writer;
    }
    
    protected function _getCollection()
    {
        /** @var $queue Vaimo_IntegrationBase_Model_Queue */
        $queue = Mage::getModel('integrationbase/queue');

        if (!$queue) {
            Mage::throwException('Could not get Queue model. Vaimo_IntegrationBase not installed?');
        }

        $collection = $queue->getCollection()
            ->applyEntityTypeCode('order')
            ->applyNotExported()
            ->applyLimit(10);

        return $collection;
    }

    protected function _createInvoice(Mage_Sales_Model_Order $order)
    {
        $this->_logs[] = 'Creating Invoice: ' . $order->getIncrementId();

        if (!$order->canInvoice()) {
            $this->_log[] = 'The order does not allow creating an invoice';
            return false;
        }

        $qtyData = array();

        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtyData);

        if (!$invoice) {
            $this->_logs[] = 'Failed to create invoice for the order';
            return false;
        }

        if (!$invoice->getTotalQty()) {
            $this->_logs[] = 'Cannot create an invoice without products';
            return false;
        }

        $invoice->register();

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        $this->_logs[] = 'Invoice created';

        // capture invoice
        if ($invoice->canCapture()) {
            $invoice->capture();

            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $this->_logs[] = 'Invoice captured';
        }

        return true;
    }

    protected function _getAttributeCode($id)
    {
        $coreRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $coreRead->select()
            ->from('eav_attribute', 'attribute_code')
         	->where('attribute_id=?', $id);
        $res = $coreRead->fetchCol($select);
        if (is_array($res)) {
            return $res[0];
        } else {
            return $id;
        }
    }

    protected function _getOptionCode($id, $options)
    {
        $res = $id;
        foreach ($options as $option) {
            if (isset($option['option_id']) && isset($option['label'])) {
                if ((int)$option['option_id']==(int)$id) {
                    $res = $option['label'];
                }
            }
        }
        return $res;
    }
    
    protected function _updateOrderWithComment($orderId, $comment)
    {
        $order = Mage::getModel('sales/order');
        $order->load($orderId);
        $order->addStatusHistoryComment(Mage::helper('integrationbasestandard')->__($comment));
        $order->save();
    }

    public function export($orderId, Varien_Object $result)
    {
        try {
            /** @var $order Mage_Sales_Model_Order */
            $include_zero = Mage::getStoreConfig(self::CONFIG_XML_PATH_ZERO_FIELDS);
            $include_blank = Mage::getStoreConfig(self::CONFIG_XML_PATH_BLANK_FIELDS);
            $line_id_separate = Mage::getStoreConfig(self::CONFIG_XML_LINE_ID_SEPARATE);

            $coreRead = Mage::getSingleton('core/resource')->getConnection('core_read');

            $order = Mage::getModel('sales/order');
            $order->load($orderId);
//            $this->_createInvoice($order);

            $this->_logs[] = 'Sending Order: ' . $order->getIncrementId();

            $this->_getXmlWriter()->AddXMLBlockStart('integrationbase');
            $version = (string)Mage::getConfig()->getNode()->modules->Vaimo_IntegrationBaseStandard->version;
            $this->_getXmlWriter()->AddXMLBlockValue('file_created',date('Y-m-d H:i'));
            $this->_getXmlWriter()->AddXMLBlockValue('source_module','Vaimo_IntegrationBaseStandard');
            $this->_getXmlWriter()->AddXMLBlockValue('version',$version);

            $this->_getXmlWriter()->AddXMLBlockStart('order');

            $this->_getXmlWriter()->AddXMLBlockStart('head');
            $this->_getXmlWriter()->AddXMLBlockData($order->getData(),$include_zero,$include_blank);
            $this->_getXmlWriter()->AddXMLBlockEnd('head');

            $this->_getXmlWriter()->AddXMLBlockStart('address');

            if ($order->getBillingAddress()) {
                $this->_getXmlWriter()->AddXMLBlockStart('billing');
                $this->_getXmlWriter()->AddXMLBlockData($order->getBillingAddress()->getData(),$include_zero,$include_blank);
                $this->_getXmlWriter()->AddXMLBlockEnd('billing');
            }

            if ($order->getShippingAddress()) {
                $this->_getXmlWriter()->AddXMLBlockStart('shipping');
                $this->_getXmlWriter()->AddXMLBlockData($order->getShippingAddress()->getData(),$include_zero,$include_blank);
                $this->_getXmlWriter()->AddXMLBlockEnd('shipping');
            }

            $this->_getXmlWriter()->AddXMLBlockEnd('address');

            if ($order->getPayment()) {
                $this->_getXmlWriter()->AddXMLBlockStart('payment');
                $data = $order->getPayment()->getData();
                unset($data['additional_information']);
                unset($data['additional_data']);
                $this->_getXmlWriter()->AddXMLBlockData($data,$include_zero,$include_blank);

                $info = unserialize($order->getPayment()->getAdditionalData());
                if ($info) {
                    $this->_getXmlWriter()->AddXMLBlockStart('additional_data');
                    $this->_getXmlWriter()->AddXMLBlockData($info,$include_zero,$include_blank);
                    $this->_getXmlWriter()->AddXMLBlockEnd('additional_data');
                }

                $info = $order->getPayment()->getAdditionalInformation();
                if ($info) {
                    $this->_getXmlWriter()->AddXMLBlockStart('additional_information');
                    $this->_getXmlWriter()->AddXMLBlockData($info,$include_zero,$include_blank);
                    $this->_getXmlWriter()->AddXMLBlockEnd('additional_information');
                }
                $this->_getXmlWriter()->AddXMLBlockEnd('payment');
            }

            $this->_getXmlWriter()->AddXMLBlockStart('items');
            $line = 1;
            foreach ($order->getAllItems() as $item) {
                if ($line_id_separate) {
                    $this->_getXmlWriter()->AddXMLBlockStart('item');
                    $this->_getXmlWriter()->AddXMLBlockValue('line',$line);
                } else {
                    $this->_getXmlWriter()->AddXMLBlockStart('item', 'line', $line);
                }
                $item_data = $item->getData();
                unset($item_data['product_options']);
                $this->_getXmlWriter()->AddXMLBlockData($item_data,$include_zero,$include_blank);

                $product_options = $item->getProductOptions();
                if (isset($product_options['info_buyRequest'])) {
                    if (isset($product_options['info_buyRequest']['super_attribute'])) {
                        foreach($product_options['info_buyRequest']['super_attribute'] as $attr_pos => $attr_val) {
                            $attr_code = $this->_getAttributeCode($attr_pos);
                            unset($product_options['info_buyRequest']['super_attribute'][$attr_pos]);
                            $product_options['info_buyRequest']['super_attribute'][$attr_code] = $attr_val;
                        }
                    }
                    if (isset($product_options['info_buyRequest']['options']) && isset($product_options['options'])) {
                        foreach($product_options['info_buyRequest']['options'] as $attr_pos => $attr_val) {
                            $attr_code = $this->_getOptionCode($attr_pos, $product_options['options']);
                            unset($product_options['info_buyRequest']['options'][$attr_pos]);
                            if (is_array($attr_val)) {
                                $attr_val = implode(',',$attr_val);
                            }
                            $product_options['info_buyRequest']['options'][$attr_code] = $attr_val;
                        }
                    }
                }
                $this->_getXmlWriter()->AddXMLBlockStart('product_options');
                $this->_getXmlWriter()->AddXMLBlockDataExpand($product_options,$include_zero,$include_blank);
                $this->_getXmlWriter()->AddXMLBlockEnd('product_options');

                $this->_getXmlWriter()->AddXMLBlockEnd('item');
                $line++;
            }
            $this->_getXmlWriter()->AddXMLBlockEnd('items');

            $this->_getXmlWriter()->AddXMLBlockEnd('order');

            $this->_getXmlWriter()->AddXMLBlockEnd('integrationbase');
            
            $this->_updateOrderWithComment($order->getId(), 'Order exported');

            $exportPath = Mage::getBaseDir('var') . DS . 'export' . DS . 'standard' . DS . 'orders' . DS;
            $this->_getXmlWriter()->exportXml($exportPath,"" . $order->getIncrementId() . ".xml");
            $result->setStatus(true);
        } catch (Exception $e) {
            $this->_logs[] = Icommerce_Utils::getTriggerLine(Icommerce_Utils::TRIGGER_STATUS_FAILED, 'Failed to export order: ' . $e->getMessage());
            $result->setStatus(true);
        }

        $this->_logs[] = '';
        $result->setLog($this->_logs);
    }
}