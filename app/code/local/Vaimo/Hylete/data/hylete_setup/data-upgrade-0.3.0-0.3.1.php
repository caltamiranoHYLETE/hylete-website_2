<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
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
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

/** @var Mage_Cms_Model_Block $block */
$block = Mage::getModel('cms/block')->load('fire-checkout-shipping-domestic');
if (!$block->getId()) {
    $block->setIdentifier('fire-checkout-shipping-domestic');

    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent('');
    $block->setTitle('Fire Checkout Shipping Domestic');
    $block->save();
}
/** @var Mage_Cms_Model_Block $block */
$block2 = Mage::getModel('cms/block')->load('fire-checkout-shipping-international');
if (!$block2->getId()) {
    $block2->setIdentifier('fire-checkout-shipping-international');

    $block2->setStores(array(0));
    $block2->setIsActive(1);
    $block2->setContent('');
    $block2->setTitle('Fire Checkout Shipping International');
    $block2->save();
}

$block = Mage::getModel('cms/block')->load('nosto-pdp-recommended-products');
if (!$block->getId()) {
    $block->setIdentifier('nosto-pdp-recommended-products');
    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent('');
    $block->setTitle('Nosto PDP Recommended Products');
    $block->save();
}
