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
 * @package     Vaimo_Menu
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Menu_Model_Group extends Vaimo_Menu_Model_Abstract
{
    const XPATH_CONFIG_MENU_TYPES = 'frontend/vaimo_menu/*/groups/*';
    const DEFAULT_GROUP = 'main';

    const DISABLED = 0;
    const ENABLED = 1;
    const MERGED = 2;

    protected $_groups = array();

    /**
     * Returns all menu types
     *
     * @return array
     */
    public function getAll()
    {
        $groupNodes = $this->_config->getXPath(self::XPATH_CONFIG_MENU_TYPES);

        $groups = array();
        foreach ($groupNodes as $group) {
            $groups[$group->getName()] = (string)$group->label;
        }

        return $groups;
    }
}