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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_MultiOptionFilter
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_MultiOptionFilter_Helper_App extends Mage_Core_Helper_Abstract
{
    /**
     * @var null|array
     */
    protected $_stateConfig = null;

    public function manipulateRequest($data)
    {
        $request = Mage::app()->getRequest();
        $originalRequest = clone $request;

        Mage::app()->setRequest($request);

        foreach ($data as $key => $value) {
            $request->setParam($key, $value);
        }

        return $originalRequest;
    }

    public function getRequestedState()
    {
        if ($this->_stateConfig === null) {
            $this->_stateConfig = Mage::helper('multioptionfilter/request')->createParamLookupTable(
                Mage::app()->getRequest(),
                array_filter(
                    explode(',', (string)Mage::getStoreConfig('multioptionfilter/settings/params_ignore_list'))
                )
            );
        }

        return $this->_stateConfig;
    }
}
