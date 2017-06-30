<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Import_File extends Vaimo_IntegrationBase_Model_Import_Abstract
{
    protected $_eventPrefix = 'file';

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/file', 'File import');
        $this->_successMessage = '%d file(s) imported';
        $this->_failureMessage = '%d file(s) failed to import';
    }

    /**
     * @param Vaimo_IntegrationBase_Model_File $baseFile
     * @return bool
     */
    protected function _importRecord($baseFile)
    {
        $this->_log($baseFile->getUrl());
        $filename = Mage::getBaseDir() . DS . $baseFile->getFilename();

        if (!is_dir(pathinfo($filename, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($filename, PATHINFO_DIRNAME), 0777, true);
        }

        $fp = fopen($filename, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseFile->getUrl());
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        fclose($fp);

        $this->_log($baseFile->getFilename() . ' | ' . filesize($filename));

        return $this;
    }

    protected function _deleteRecord($item)
    {
        Mage::throwException('File import does not support delete');
    }
}