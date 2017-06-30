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

class Vaimo_BlockAjax_Model_PageCache_Processor
{
    protected $_request;
    protected $_processorDelegate;

    protected function _getRequest()
    {
        if ($this->_request === null) {
            $this->_request = new Zend_Controller_Request_Http();
        }

        return $this->_request;
    }

    protected function _isBlockAjaxRequest()
    {
        $request = $this->_getRequest();
        $analyzer = new Vaimo_BlockAjax_Helper_Request();

        return $analyzer->isBlockAjaxRequest($request);
    }

    protected function _getProcessorDelegate()
    {
        if (!@class_exists('Enterprise_PageCache_Model_Processor')) {
            return false;
        }

        if (!$this->_processorDelegate) {
            $this->_processorDelegate = new Enterprise_PageCache_Model_Processor();
        }

        return $this->_processorDelegate;
    }

    public function extractContent($content)
    {
        if (!($delegate = $this->_getProcessorDelegate())) {
            return false;
        }

        if ($this->_isBlockAjaxRequest()) {
            $requestId = false;

            if (isset($_GET[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM])) {
                $requestId = $_GET[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM];
                unset($_GET[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM]);
            }

            $content = $delegate->extractContent($content);

            if (!$content && $content !== false) {
                /**
                 * If we end up with blank content - these need to be unset - otherwise we'll end up crashing when
                 * the actually Enterprise processor kicks in
                 */
                Mage::unregister('cached_page_content');
                Mage::unregister('cached_page_containers');
            }

            if ($content) {
                $serializer = new Vaimo_BlockAjax_Model_Response_Serializer();
                $response = $serializer->deserialize($content);

                $content = Vaimo_BlockAjax_Json_Encoder::encode($response);
            } else if ($requestId) {
                $_GET[Vaimo_BlockAjax_Helper_Data::AJAX_REQUEST_ID_PARAM] = $requestId;
            }

            return $content;
        }

        return false;
    }
}