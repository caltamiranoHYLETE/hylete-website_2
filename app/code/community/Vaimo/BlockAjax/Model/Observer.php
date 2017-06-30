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

class Vaimo_BlockAjax_Model_Observer
{
    public function disableOutput()
    {
        $controller = Mage::app()->getFrontController();
        $request = $controller->getRequest();

        if (!Mage::helper('blockajax/request')->isBlockAjaxRequest($request)) {
            return;
        }

        $layout = Mage::app()->getLayout();

        foreach (array_keys($layout->getAllBlocks()) as $blockName) {
            $layout->removeOutputBlock($blockName);
        }
    }

    public function generateResponse()
    {
        $action = Mage::app()->getFrontController()->getAction();
        $response = $action->getResponse();

        if ($response->getBody()) {
            return;
        }

        $request = $action->getRequest();
        if (!Mage::helper('blockajax/request')->isBlockAjaxRequest($request)) {
            return;
        }

        $responseData = Mage::getSingleton('blockajax/request_handler')->getResponse($request);

        if (Mage::helper('blockajax')->isEnterprisePageCache()) {
            $responseData = Mage::getModel('blockajax/response_serializer')->serialize($responseData);
        } else {
            $responseData = Mage::getSingleton('blockajax/request_handler')->encodeWithLatestRequestId($responseData);
        }

        $response->setBody($responseData);
    }

    public function postProcessResponse(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('blockajax')->isEnterprisePageCache()) {
            return;
        }

        $controller = Mage::app()->getFrontController();
        $request = $controller->getRequest();

        if (!Mage::helper('blockajax/request')->isBlockAjaxRequest($request)) {
            return;
        }

        $response = $observer->getResponse();
        if ($body = $response->getBody()) {
            $responseData = Mage::getSingleton('blockajax/response_serializer')->deserialize($body);
            $responseData = Mage::getSingleton('blockajax/request_handler')->encodeWithLatestRequestId($responseData);

            $response->setBody($responseData);
        }
    }
}