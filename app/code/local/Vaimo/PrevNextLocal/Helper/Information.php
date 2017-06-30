<?php
/**
 * Copyright(c) 2009 - 2013 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered . The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence . A licence
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
 * @package     Vaimo_PrevNextLocal
 * @file        Data.php
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */

class Vaimo_PrevNextLocal_Helper_Information extends Mage_Core_Helper_Abstract
{
    /**
     * Get module version
     * http://www.magentron.com/blog/2011/07/20/how-to-display-your-extension-version-in-magento-admin#sthash.TgcjB1Rw.dpuf
     * @return string
     */
    public function getExtensionVersion() {
        return (string) Mage::getConfig()->getNode()->modules->Vaimo_PrevNextLocal->version;
    }

    public function getExtensionDocumentation() {
        return '<a href="http://confluence.vaimo.com/display/CUSEXP/Vaimo_PrevNextLocal" target="_blank">Confluence</a>';
    }

    public function getExtensionCode() {
        return '<a href="https://bitbucket.org/vaimo/vaimo_prevnextlocal" target="_blank">Bitbucket</a>';
    }

}
