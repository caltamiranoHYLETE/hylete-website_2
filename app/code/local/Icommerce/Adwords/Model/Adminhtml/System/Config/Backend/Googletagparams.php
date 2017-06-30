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
 * @category      Vaimo
 * @package       Icommerce_Adwords
 * @copyright     Copyright (c) 2009-2015 Vaimo AB
 * @author        Branislav Jovanovic <branislav.jovanovic@vaimo.com>
 * @author        Simen Thorsrud <simen.thorsrud@vaimo.com>
 */

/**
 * Class Icommerce_Adwords_Model_Adminhtml_System_Config_Backend_Googletagparams
 *
 * This class is responsible for getting and saving Google tag params configuration value in useful format. Its main
 * function is to automatically serialize and unserialize values.
 */
class Icommerce_Adwords_Model_Adminhtml_System_Config_Backend_Googletagparams
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    /**
     * Removes redundant values before saving into db config
     */
    protected function _beforeSave()
    {
        $googleTagParams = $this->getValue();
        if (is_array($googleTagParams)) {
            unset($googleTagParams['__empty']);

            $params = array();
            foreach ($googleTagParams as $key => $val) {
                // Remove duplicate params
                if (isset($val['google_tag_param_name']) && in_array($val['google_tag_param_name'], $params)) {
                    unset($googleTagParams[$key]);
                } else {
                    $params[] = $val['google_tag_param_name'];
                }
            }

            $this->setValue($googleTagParams);
            parent::_beforeSave();
        }
    }

    /**
     * Automatically unserialized string when loading value
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            if (@unserialize($this->getValue())) {
                $val = unserialize($this->getValue());
                $this->setValue($val);
            }
        }
    }
}
