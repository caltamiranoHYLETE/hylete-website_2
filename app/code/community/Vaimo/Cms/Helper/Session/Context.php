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
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Helper_Session_Context extends Vaimo_Cms_Helper_Abstract
{
    public function execute($namespace, $callable)
    {
        if ($namespace == Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE) {
            $this->executeInAdmin($callable);
            return;
        }

        /** @var Vaimo_Cms_Helper_Session $sessionHelper */
        $sessionHelper = $this->getFactory()->getHelper('vaimo_cms/session');

        $session = $sessionHelper->start($namespace);

        $callable($session);

        $sessionHelper->close();
    }

    private function executeInAdmin($callable)
    {
        /** @var Mage_Core_Model_Store $oldStore */
        $oldStoreApp = $this->getApp()->getStore();

        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = $this->getFactory()->getSingleton('core/cookie');
        $oldStoreCookie = $cookie->getStore();

        $this->getApp()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $cookie->setStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        /** @var Vaimo_Cms_Helper_Session $sessionHelper */
        $sessionHelper = $this->getFactory()->getHelper('vaimo_cms/session');

        $session = $sessionHelper->start(Mage_Adminhtml_Controller_Action::SESSION_NAMESPACE);

        $callable($session);

        $sessionHelper->close();

        $cookie->setStore($oldStoreCookie);
        $this->getApp()->setCurrentStore($oldStoreApp->getCode());
    }
}
