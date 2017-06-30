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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_WebController extends Mage_Core_Controller_Front_Action
{
    /**
     * Post to this action to set a session flag and then redirect to the webpage
     * you want to view.
     *
     * Expects webpage
     *
     * @return void
     */
    public function browseAction()
    {
        $webpage = $this->_request->getParam('webpage', null);
        $checkout = Mage::getSingleton('checkout/session');
        $checkout->setAppBrowseFlag(true);
        $newpage = Mage::getUrl($webpage);
        $this->_response
            ->setRedirect($newpage)
            ->sendResponse();
        exit;
    }

}