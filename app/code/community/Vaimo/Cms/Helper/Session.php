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

class Vaimo_Cms_Helper_Session extends Vaimo_Cms_Helper_Abstract
{
    const FORM_KEY_PARAMETER = '_form_key';
    const SECURE_COOKIE_PREFIX = '_cid';

    public function start($namespace)
    {
        $factory = $this->getFactory();

        $runtimeHelper = $factory->getHelper('vaimo_cms/runtime');

        if ($runtimeHelper->singletonExists('core/session')) {
            throw Mage::exception('Vaimo_Cms', 'Core session singleton already instantiated');
        }

        return $factory->getSingleton('core/session', array(
            'name' => $namespace
        ));
    }

    public function close()
    {
        $runtimeHelper = $this->getFactory()->getHelper('vaimo_cms/runtime');

        session_write_close();
        $runtimeHelper->unsetSingleton('core/session');

        unset($_SESSION);

        if ($runtimeHelper->functionExists('apache_response_headers')) {
            foreach (array_keys(apache_response_headers()) as $key) {
                header_remove($key);
            }
        }
    }

    public function unsetByName($namespace)
    {
        $cookie = $this->getFactory()->getSingleton('core/cookie');

        $cookie->delete($namespace);
        $cookie->delete($namespace . self::SECURE_COOKIE_PREFIX);
    }

    public function setId($sessionId)
    {
        if ($sessionId) {
            session_id($sessionId);
        }
    }
}