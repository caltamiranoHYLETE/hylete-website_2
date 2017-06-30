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

/**
 * Class Vaimo_Menu_Model_Layout_Analyser
 *
 * @method object setArea(string $area)
 * @method object setPackage(string $package)
 * @method object setTheme(string $package)
 */
class Vaimo_Menu_Model_Layout_Analyser extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Chooser_Block
{
    protected $_widgetContainerXpathBase = '//block/label';

    public function getWidgetContainersForHandle($handle, $storeId, $xPath = '')
    {
        /**
         * We want analyser to skip the internal instance caching which happens with this protected attribute
         */
        $this->_blocks = array();

        $design = Mage::helper('vaimo_menu')->getConfiguredDesignInformationForStore($storeId);
        $this->setLayoutHandle(array($handle));
        $this->setArea(Mage_Core_Model_Design_Package::DEFAULT_AREA);
        $this->setPackage($design->getPackage());
        $this->setTheme($design->getPackage());
        $this->getBlocks();

        $foundBlocks = $this->_layoutHandleUpdatesXml->xpath($this->_widgetContainerXpathBase . $xPath . '/..');

        $allowedBlocks = array();
        foreach ($foundBlocks as $block) {
            if (($name = (string)$block->getAttribute('name')) && $this->_filterBlock($block)) {
                $allowedBlocks[$name] = $block;
            }
        }

        return $allowedBlocks;
    }
}