<?php

/**
 * Copyright(c) 2009 - 2015 Vaimo
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
 * @file     Observer.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */
Class Vaimo_PrevNextLocal_Model_Observer
{

    /**
     * Triggers when the admin config are saved for this module.
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function SaveConfig(Varien_Event_Observer $observer)
    {
        $fileName = Mage::getBaseDir() . DS . 'trigger' . DS . 'prevnextlocal' . DS . 'config.json';
        $data = $_POST['groups'];
        $data['base'] = array(
            'url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
            'image_url' => Mage::getBaseUrl('media'),
            'dir' => Mage::getBaseDir(),
            'image_dir' => Mage::getBaseDir('media'),
            'cache_path' => $data['images']['fields']['cache_path']['value']
        );
        $jsonData = json_encode($data);
        file_put_contents($fileName, $jsonData);
    }

}
