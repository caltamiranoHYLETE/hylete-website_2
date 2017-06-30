<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @category    Icommerce
 * @package     Icommerce_OrderStatus
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

/** @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;

/**
 * Install/Update order statuses from config
 */
$data     = array();
$statuses = Mage::getConfig()->getNode('global/sales/order/statuses')->asArray();
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status' => $code,
        'label'  => $info['label']
    );
}
$installer->getConnection()->insertOnDuplicate(
    $installer->getTable('sales/order_status'),
    $data,
    array('status', 'label')
);

/**
 * Install/Update order statuses from config
 */
$data   = array();
$states = Mage::getConfig()->getNode('global/sales/order/states')->asArray();

foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = array(
                'status'     => $status,
                'state'      => $code,
                'is_default' => is_array($statusInfo) && isset($statusInfo['@']['default']) ? 1 : 0
            );
        }
    }
}
$installer->getConnection()->insertOnDuplicate(
    $installer->getTable('sales/order_status_state'),
    $data,
    array('status', 'state', 'is_default')
);
