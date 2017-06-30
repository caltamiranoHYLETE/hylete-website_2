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

class Vaimo_IntegrationBase_Model_Process extends Vaimo_IntegrationBase_Model_Abstract
{
    protected $_queue = null;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('integrationbase/process');
    }

    /**
     * Set user data for the custom process
     *
     * @param array $data
     * @return Varien_Object
     */
    public function setProcessData(array $data)
    {
        return $this->_setComplexData('process_data', $data);
    }

    /**
     * Get user data for the custom process
     *
     * @param null|string $valueKey Gives the oportunity to fetch a single values from the serialized data
     * @return array|mixed|string
     */
    public function getProcessData($valueKey = null)
    {
        return $this->_getComplexData('process_data', $valueKey);
    }

    /**
     * Get queue instance
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function getQueue()
    {
        if (!$this->_queue) {
            $this->_queue = Mage::getModel('integrationbase/queue');
        }

        return $this->_queue;
    }

    /**
     * Queue a process with certain code and user data
     *
     * @param $type Process code (used in process queue and triggers event with a name of integrationbase_process_queue_<type>)
     * @param array $args   Process user data
     * @return $this
     */
    public function queue($type, $args = array())
    {
        if (is_array($args)) {
            $this->setProcessData($args);
            $this->save();
            $this->getQueue()->add($this, $type);
        }

        return $this;
    }

    /**
     * Load process data entity from the process queue
     *
     * @param $queueItem
     * @return mixed
     */
    public function loadFromQueue($queueItem)
    {
        return $queueItem->getEntityFromQueueItem($this->getResourceName());
    }

    /**
     * Save the process data entity (used for unit testing)
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        return parent::save();
    }
}
