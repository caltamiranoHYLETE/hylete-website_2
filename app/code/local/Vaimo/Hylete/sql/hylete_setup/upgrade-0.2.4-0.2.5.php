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

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$content = <<<EOF
<p><strong>Quicklinks:</strong></p>
<p><a>Products</a></p>
<p><a>V-Necks</a></p>
<p><a>Tops</a></p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Quick Links')
        ->setIdentifier('quick_links')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$content = <<<EOF
<p>up to <strong>40% off</strong> entire order. <a href="account/login/">join #HYLETEnation</a></p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Mobile Cart Login')
        ->setIdentifier('mobile_cart_login')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$content = <<<EOF
<p>Menu bottom desktop block</p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Menu Bottom Block - Desktop')
        ->setIdentifier('menu_bottom_block_desktop')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$content = <<<EOF
<p>Menu bottom mobile block</p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Menu Bottom Block - Mobile')
        ->setIdentifier('menu_bottom_block_mobile')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$content = <<<EOF
<p>Join HYLETEnation text…</p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Login Page - New customer text - HYLETEnation')
        ->setIdentifier('login_page_hyletenation')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$content = <<<EOF
<p><strong>get the look</strong><img alt="" src="" />see all looks</p>
EOF;

try {
    Mage::getModel('cms/block')
        ->setTitle('Menu Mens Get The Look')
        ->setIdentifier('menu_mens_get_the_look')
        ->setContent($content)
        ->setStores(array(0))
        ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();