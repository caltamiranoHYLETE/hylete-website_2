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

class Vaimo_Menu_Model_Type extends Vaimo_Menu_Model_Abstract
{
    const DIRECTION_HORIZONTAL = 'horizontal';
    const DIRECTION_VERTICAL = 'vertical';

    const COLUMNS = 'columns';
    const ROWS = 'rows';

    const ITEMS_VERTICAL_SLIDE = 'vertical_slide';
    const ITEMS_HORIZONTAL_SLIDE = 'horizontal_slide';
    const ITEMS_NESTED = 'nested';
    const ITEMS_NONE = null;

    const XPATH_CONFIG_MENU_TYPES = 'frontend/vaimo_menu';
    const XPATH_CONFIG_SELECTED_MENU_TYPE = 'vaimo_menu/settings/type';
    const MENU_TYPE_LAYOUT_UPDATE_PREFIX = 'vaimomenu_type';

    protected $_typeDefinitions = array();

    /**
     * Returns all menu types
     *
     * @return array
     */
    public function getAll()
    {
        if (!$this->_typeDefinitions) {
            $typesNode = $this->getConfig()->getXPath(self::XPATH_CONFIG_MENU_TYPES);
            $typeConfigurations = (array)$typesNode[0];

            $types = array();
            foreach ($typeConfigurations as $code => $configuration) {
                if ($code != 'default') {
                    $types[$code] = (array)$configuration;
                }
            }
            $this->_typeDefinitions = $types;
        }

        return $this->_typeDefinitions;
    }

    /**
     * Get menu configuration for one type code
     *
     * @param $code
     * @return mixed
     *
     * @throws Exception
     */
    public function getDefinitionByCode($code)
    {
        $types = $this->getAll();

        if (!isset($types[$code])) {
            throw Mage::exception('Vaimo_Menu', 'Type does not exist', Vaimo_Menu_Exception::TYPE_NOT_FOUND);
        }

        return $types[$code];
    }

    /**
     * Returns layout configuration for the menu per-level
     *
     * @param $code
     * @return array
     */
    public function getNavigationBlockType($code)
    {
        $definition = $this->getDefinitionByCode($code);

        return $definition['type'];
    }
}