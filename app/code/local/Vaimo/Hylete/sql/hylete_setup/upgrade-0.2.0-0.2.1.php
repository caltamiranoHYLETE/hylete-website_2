<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */
$customAttributes = array(
    'motivation_join' => array(
        'option' => array(
            100 => 'Other'
        )
    ),
    'first_heard_event' => array(
        'option' => array(
            100 => 'Other'
        )
    ),
    'first_heard_mag' => array(
        'option' => array(
            100 => 'Other'
        )
    ),
    'first_heard_pers' => array(
        'option' => array(
            100 => 'Other'
        )
    ),
    'first_heard_org' => array(
        'option' => array(
            100 => 'Other'
        )
    ),
    'first_heard_event_o' => array(
        'label' => 'What Event?',
        'input' => 'text',
    ),
    'first_heard_mag_o' => array(
        'label' => 'What Magazine?',
        'input' => 'text',
    ),
    'first_heard_pers_o' => array(
        'label' => 'Who?',
        'input' => 'text',
    ),
    'first_heard_org_o' => array(
        'label' => 'What Organization?',
        'input' => 'text',
    ),
    'first_heard_ad_o' => array(
        'label' => 'What ad?',
        'input' => 'text',
    ),
);
/* @var $installer Vaimo_Hylete_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();
try {
    foreach ($customAttributes as $attributeKey => $attributeSettings) {
        $installer->addOrUpdateCustomerAttributes($attributeKey, $attributeSettings);
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$installer->endSetup();