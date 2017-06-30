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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_Widget_ManagerPool extends Vaimo_Cms_Model_Editor_Abstract
{
    /**
     * @var null|array
     */
    protected $items;

    /**
     * @param $widgetType
     * @return array
     */
    public function getConfigForType($widgetType)
    {
        if ($this->items === null) {
            $items = array(
                'cms/widget_block' => array(
                    'type' => 'vaimo_cms/widget_managers_cmsBlock',
                    'defaults' => array(
                        'template' => 'cms/widget/static_block/default.phtml'
                    )
                )
            );

            $transport = new Varien_Object(array(
                'creators' => $items
            ));

            Mage::dispatchEvent('vaimo_cms_prepare_managers_config', array(
                'transport' => $transport
            ));

            $this->items = $transport->getCreators();
        }

        if (!isset($this->items[$widgetType]) || !is_array($this->items[$widgetType])) {
            return array();
        }

        return $this->items[$widgetType];
    }
}
