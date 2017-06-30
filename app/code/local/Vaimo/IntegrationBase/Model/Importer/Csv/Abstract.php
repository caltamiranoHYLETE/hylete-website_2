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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_IntegrationBase
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 */

abstract class Vaimo_IntegrationBase_Model_Importer_Csv_Abstract
{
    protected $_eventPrefix = 'abstract';
    protected $_delimiter = ',';
    protected $_enclosure = '"';
    protected $_errors = array();
    protected $_successCount = 0;
    protected $_failureCount = 0;

    abstract protected function _importRow($data);

    public function import($filename)
    {
        ini_set('auto_detect_line_endings', 1);
        $fp = fopen($filename, 'r');
        $headers = fgetcsv($fp, null, $this->_delimiter, $this->_enclosure);
        $i = 0;

        while ($row = fgetcsv($fp, null, $this->_delimiter, $this->_enclosure)) {
            $i++;
            try {
                $data = @array_combine($headers, $row);
                if (!$data) {
                    Mage::throwException('Number of columns doesn\'t match with the header');
                }

                $object = new Varien_Object($data);
                Mage::dispatchEvent('integrationbase_import_csv_' . $this->_eventPrefix . '_before', array('object' => $object));
                $data = $object->getData();

                $this->_importRow($data);
                $this->_successCount++;
            } catch (Exception $e) {
                $this->_failureCount++;
                $this->_errors[] = 'Error in row ' . $i . ': ' . $e->getMessage();
            }
        }

        fclose($fp);
        return $this;
    }

    public function getSuccessCount()
    {
        return $this->_successCount;
    }

    public function getFailureCount()
    {
        return $this->_failureCount;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}