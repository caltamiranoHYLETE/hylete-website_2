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

class Vaimo_Cms_Model_Widget_Editor_Cloner extends Vaimo_Cms_Model_Editor_Abstract
{
    protected $_actions = array(
        Vaimo_Cms_Model_Widget_Editor::UPDATE_ACTION => 'cloneStructureForWidget'
    );

    protected $_require = array(
        'widget_structure_id',
        'widget_page_id'
    );

    public function cloneStructureForWidget(&$arguments)
    {
        $helper = $this->getFactory()->getHelper('vaimo_cms/structure');

        $widget = $helper->getStructureWidgetStoreView(
            $arguments['widget_structure_id'],
            $arguments['widget_page_id'],
            $this->getStoreId()
        );

        if ($arguments['widget_page_id'] != $widget['widget_page_id'] && isset($widget['clone_of'])) {
            $arguments['targeted_page_id'] = $widget['clone_of'];
        }

        $arguments['widget_page_id'] = $widget['widget_page_id'];
    }
}