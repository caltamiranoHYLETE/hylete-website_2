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

class Vaimo_MultiOptionFilter_Model_Layer_ProductCollectionUtils
{
    /**
     * @var Vaimo_MultiOptionFilter_Model_Resource_Query_Manipulator
     */
    protected $_queryManipulator;

    /**
     * @var array
     */
    protected $_ignoredTables;

    public function __construct()
    {
        $this->_queryManipulator = Mage::getResourceSingleton('multioptionfilter/query_manipulator');
    }

    public function synchronize($origin, $target)
    {
        if ($this->_ignoredTables === null) {
            $transport = new Varien_Object(array(
                'ignored_tables' => array('price_index')
            ));

            Mage::dispatchEvent('vaimo_multioption_filter_ignored_tables_collect', array(
                'transport' => $transport
            ));

            $this->_ignoredTables = $transport->getData('ignored_tables');
        }
        
        $this->_queryManipulator->copyFilter(
            $origin->getSelect(),
            $target->getSelect(),
            $this->_ignoredTables
        );
    }
}
