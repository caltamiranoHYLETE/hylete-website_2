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
        'label' => 'What motivated you to join the HYLETE nation? (check all that apply)',
        'input' => 'multiselect',
        'options' => array(
            'Quality of HYLETE apparel and gear',
            'Value(price) of HYLETE apparel and gear',
            'Friend recommendation',
            'I believe in the HYLETE nation',
        )
    ),
    'motivation_join_other' => array(
        'label' => 'Other',
        'input' => 'text',
        'options' => ''
    ),
    'first_heard' => array(
        'label' => 'How did you first hear about HYLETE?',
        'input' => 'select',
        'options' => array(
            'Event',
            'Magazine',
            'Person',
            'Organization',
            'Online AD'
        )
    ),
    'first_heard_event' => array(
        'label' => 'What event?',
        'input' => 'select',
        'options' => array(
            'WODapaloza',
            'Heart of America',
            'L.A. fit expo',
            'Arnold Fitness Expo',
            'Granite Games',
            'Beacon Beatdown',
        )
    ),
    'first_heard_mag' => array(
        'label' => 'What magazine?',
        'input' => 'select',
        'options' => array(
            'Men\'s Health',
            'Men\'s Fitness',
            'Oxygen',
            'WODtalk ',
        )
    ),
    'first_heard_pers' => array(
        'label' => 'Who?',
        'input' => 'select',
        'options' => array(
            'Friend',
            'Trainer',
            'Gym owner',
            'Robb Wolf',
        )
    ),
    'first_heard_org' => array(
        'label' => 'What organization?',
        'input' => 'select',
        'options' => array(
            'NASM',
            'Dragon Door',
        )
    ),
    'first_heard_ad' => array(
        'label' => 'What ad?',
        'input' => 'select',
        'options' => array(
            'Campaign 1',
            'Campaign 2'
        )
    )
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