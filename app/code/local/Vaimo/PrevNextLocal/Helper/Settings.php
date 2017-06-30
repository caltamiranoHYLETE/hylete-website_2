<?php
/**
 * Copyright(c) 2009 - 2014 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */

class Vaimo_PrevNextLocal_Helper_Settings extends Mage_Core_Helper_Abstract
{
    public function getSettings() {
        $settings = array();
        $base = 'prevnextlocal';
        $paths = array(
            'information' => array('version','documentation','code'),
            'general' => array('enable'),
            'include' => array('title','shortdescription','image','colours'),
            'collection' => array('product_types'),
            'scene7' => array('enable','helper')
        );

        foreach ($paths as $groupName => $fields) {
            foreach ($fields as $fieldName) {
                $path = $base .'/'. $groupName .'/'. $fieldName;
                $setting = Mage::getStoreConfig($path);
                if (is_null($setting)) {
                    $setting = false;
                }
                $key = $groupName . '_' . $fieldName;
                $settings[$key] = $setting;
            }
        }

        $settings['information_version'] = (string) Mage::helper('prevnextlocal/information')->getExtensionVersion();
        // $settings['information_documentation'] = (string) Mage::helper('prevnextlocal/information')->getExtensionDocumentation();
        // $settings['information_code'] = (string) Mage::helper('prevnextlocal/information')->getExtensionCode();

        return $settings;
    }
}
