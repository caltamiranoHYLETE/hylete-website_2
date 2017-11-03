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
$block = Mage::getModel('cms/block')->load('cart-above-checkout-button');
if (!$block->getId()) {
    $block->setIdentifier('cart-above-checkout-button');
    $block->setStores(array(0));
    $block->setIsActive(1);
    $block->setContent('');
    $block->setTitle('Cart - Above Checkout Button');
    $block->save();
}
/** @var Mage_Cms_Model_Block $block */
$block2 = Mage::getModel('cms/block')->load('cart-below-checkout-button');
if (!$block2->getId()) {
    $block2->setIdentifier('cart-below-checkout-button');
    $block2->setStores(array(0));
    $block2->setIsActive(1);
    $block2->setContent('');
    $block2->setTitle('Cart - Below Checkout Button');
    $block2->save();
}
