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
 * @package     Vaimo_BlockAjax
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Vaimo_BlockAjax_Helper_Data extends Mage_Core_Helper_Abstract
{
    const AJAX_REQUEST_PARAM = 'block_ajax';
    const AJAX_REQUEST_ID_PARAM = 'request_id';
    const AJAX_TAG_NAME = 'ajax';
    const AJAX_REQUEST_CONTAINER_SELECTOR_ATTRIBUTE = 'container_selector';
    const AJAX_REQUEST_CONTAINER_JAVASCRIPT_ATTRIBUTE = 'script';

    public function isBlockAjaxRequest($request)
    {
        return $request->isXmlHttpRequest() && $request->getParam(self::AJAX_REQUEST_PARAM, false);
    }

    public function isEnterprise()
    {
        return Mage::helper('core')->isModuleEnabled('Enterprise_PageCache');
    }

    public function isEnterprisePageCache()
    {
        return $this->isEnterprise() && Mage::app()->useCache('full_page');
    }
}