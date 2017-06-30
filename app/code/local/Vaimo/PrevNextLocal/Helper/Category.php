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

class Vaimo_PrevNextLocal_Helper_Category extends Mage_Core_Helper_Abstract
{

    public function getScript($productCollection)
    {
        $settings = $this->getSettings();
        if ($settings['general_enable'] != true) {
            return '';
        }

        $productCollectionIds = $productCollection->getAllIds();
        $productCollectionArrayJson = json_encode($productCollectionIds);

        $scriptCode = $this->getScriptCode($productCollectionArrayJson);
        return $scriptCode;
    }

    public function getSettings() {
        return Mage::helper('prevnextlocal/settings')->getSettings();
    }

    private function getScriptCode($productCollectionArrayJson) {
        $checkSum = md5($productCollectionArrayJson);
        $scriptCode = '<script type="text/javascript">
            jQuery( document ).ready(function() {
                if(typeof(Storage) == "undefined") {
                    return;
                }
                var $checkSum = localStorage.getItem("vaimo_prevnextlocal_collection_checksum");
                if ($checkSum == "'.$checkSum.'") {
                    return;
                }
                localStorage.setItem("vaimo_prevnextlocal_collection", \'' . $productCollectionArrayJson . '\');
                localStorage.setItem("vaimo_prevnextlocal_collection_checksum", "'.$checkSum.'");
                localStorage.removeItem("vaimo_prevnextlocal_collection_index");
            });
        </script>';
        return $scriptCode;
    }
}
