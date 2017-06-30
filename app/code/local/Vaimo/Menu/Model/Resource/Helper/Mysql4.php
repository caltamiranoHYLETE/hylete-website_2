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
 * @category    Vaimo
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

class Vaimo_Menu_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{
    protected $_partCodes = array(
        Zend_Db_Select::DISTINCT,
        Zend_Db_Select::COLUMNS,
        Zend_Db_Select::UNION,
        Zend_Db_Select::FROM,
        Zend_Db_Select::WHERE,
        Zend_Db_Select::GROUP,
        Zend_Db_Select::HAVING,
        Zend_Db_Select::ORDER,
        Zend_Db_Select::LIMIT_COUNT,
        Zend_Db_Select::LIMIT_OFFSET,
        Zend_Db_Select::FOR_UPDATE
    );

    public function copySelect($from, $to)
    {
        foreach ($this->_partCodes as $partCode) {
            $to->setPart($partCode, $from->getPart($partCode));
        }
    }
}