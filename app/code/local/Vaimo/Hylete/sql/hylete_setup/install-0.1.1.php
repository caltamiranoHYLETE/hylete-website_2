<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$blocks = array(
    array(
        'title'         => 'Footer - info',
        'identifier'    => 'footer_info',
        'stores'        => array(0),
        'content'       => '
            <p><a href="#">about HYLETE</a></p>
            <p><a href="#">#HYLETEnation</a></p>
            <p><a href="#">contact us</a></p>
            <p><a href="#">returns / exchanges</a></p>
            <p><a href="#">shipping information</a></p>
            <p><a href="#">product warranty</a></p>
        '
    ),
    array(
        'title'         => 'Footer - teams',
        'identifier'    => 'footer_teams',
        'stores'        => array(0),
        'content'       => '
            <p><a href="#">certified trainers</a></p>
            <p><a href="#">competitive athletes</a></p>
            <p><a href="#">service personnel</a></p>
        '
    ),
    array(
        'title'         => 'Footer - powered by HYLETE',
        'identifier'    => 'footer_powered_by_hylete',
        'stores'        => array(0),
        'content'       => '
            <p><a href="#">training facility</a></p>
            <p><a href="#">event sponsorship</a></p>
            <p><a href="#">co-branding</a></p>
            <p><a href="#">online affiliate</a></p>
            <p><a href="#">reseller</a></p>
            <p><a href="#">nonprofit</a></p>
        '
    )
);

foreach($blocks as $block) {
    try {
        Mage::getModel('cms/block')
            ->setStoreId($block['stores'][0])
            ->load($block['identifier'])
            ->setTitle($block['title'])
            ->setIdentifier($block['identifier'])
            ->setStores($block['stores'])
            ->setContent($block['content'])
            ->save();
    } catch (Exception $e) {
        Mage::logException($e);
    }
}

$installer->endSetup();