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
use Mage_Adminhtml_Controller_Action as AdminAction;
use Mage_Core_Controller_Front_Action as FrontendAction;

class Vaimo_Cms_Helper_Admin_Session extends Vaimo_Cms_Helper_Abstract
{
    public function instantiateInIsolation()
    {
        $factory = $this->getFactory();

        $cookie = $factory->getSingleton('core/cookie');
        $sessionContextHelper = $factory->getHelper('vaimo_cms/session_context');
        $sessionHelper = $factory->getHelper('vaimo_cms/session');
        $runtimeHelper = $factory->getHelper('vaimo_cms/runtime');

        $frontendSessionId = $cookie->get(FrontendAction::SESSION_NAMESPACE);

        $sessionContextHelper->execute(AdminAction::SESSION_NAMESPACE, function($session) use ($runtimeHelper) {
            $adminSession = $runtimeHelper->getNewSingleton('admin/session');
            $adminSession->setData(Vaimo_Cms_Helper_Session::FORM_KEY_PARAMETER, $session->getFormKey());
        });

        if (!$factory->getSingleton('admin/session')->isLoggedIn()) {
            $sessionHelper->unsetByName(AdminAction::SESSION_NAMESPACE);
        }

        if ($this->getApp()->getStore()->isFrontUrlSecure()) {
            $sessionHelper->setId($frontendSessionId);
            $sessionContextHelper->execute(FrontendAction::SESSION_NAMESPACE, function() {});
        } else {
            $sessionContextHelper->execute(FrontendAction::SESSION_NAMESPACE, function() use ($sessionHelper, $frontendSessionId) {
                $sessionHelper->setId($frontendSessionId);
            });
        }
    }

    public function executeWithAdminSessionData($keys, $callable)
    {
        $factory = $this->getFactory();

        $coreSession = $factory->getSingleton('core/session');
        $adminSession = $factory->getSingleton('admin/session');
        $runtimeHelper = $factory->getHelper('vaimo_cms/runtime');

        $coreSession->getFormKey();

        return $runtimeHelper->executeWithTmpReplacedModelData($adminSession, $coreSession, $keys, $callable);
    }
}