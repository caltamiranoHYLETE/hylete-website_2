<?php
/**
 * Copyright (c) 2009-2013 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2013 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 */

class Vaimo_IntegrationBase_Model_Export_Queue extends Vaimo_IntegrationBase_Model_Export_Abstract
{
    public function export(array $codes = array(), $limit = 0, $operationId = 0)
    {
        $result = new Varien_Object();
        /** @var $collection Vaimo_IntegrationBase_Model_Resource_Queue_Collection */
        $collection = Mage::getModel('integrationbase/queue')->getCollection();
        $collection->applyNotExported();

        if (count($codes)) {
            $collection->applyEntityTypeCode($codes);
        }

        if ($limit) {
            $collection->applyLimit($limit);
        }

        $progressMin = 0;
        $progressMax = count($collection);
        $progressPos = 0;

        foreach ($collection as $queue) {
            try {
                $name = 'integrationbase_process_queue_' . $queue->getEntityTypeCode();

                $result->setData(array(
                    'status' => null,
                    'log' => array()
                ));

                Mage::dispatchEvent($name, array(
                    'queue'  => $queue,
                    'result' => $result,
                ));

                foreach ($result->getLog() as $message) {
                    $this->_log($message);
                }

                if ($result->getStatus()) {
                    $queue->setAsExported()->save();
                    $this->_successCount++;
                } else {
                    $this->_failureCount++;
                }

                if ($operationId) {
                    Mage::helper('scheduler')->setOperationProgress($operationId, $progressMin, $progressMax, ++$progressPos);
                }
            } catch (Exception $e) {
                $this->_failureCount++;
                $this->_log($e->getMessage());
            }
        }
    }
}