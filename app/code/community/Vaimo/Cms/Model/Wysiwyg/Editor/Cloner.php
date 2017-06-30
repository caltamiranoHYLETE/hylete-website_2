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

class Vaimo_Cms_Model_Wysiwyg_Editor_Cloner extends Vaimo_Cms_Model_Editor_Abstract
{
    protected $_actions = array(
        Vaimo_Cms_Model_Wysiwyg_Editor::UPDATE_ACTION => 'cloneStructureForCmsBlock'
    );

    protected $_require = array(
        'updates'
    );

    public function cloneStructureForCmsBlock(&$arguments)
    {
        $helper = $this->getFactory()->getHelper('vaimo_cms/structure');
        $storeId = $this->getStoreId();

        foreach ($arguments['updates'] as &$update) {
            if (!isset($update['structure_id']) || !isset($update['vcms_widget_page_id'])) {
                continue;
            }

            $widget = $helper->getStructureWidgetStoreView(
                $update['structure_id'],
                $update['vcms_widget_page_id'],
                $storeId
            );

            if (!$widget['widget_parameters']) {
                return $this->_error('Cloning failed, widget parameters for CMS block were blank');
            }

            $update['block_id'] = $widget['widget_parameters']['block_id'];

            unset($update);
        }

        return null;
    }
}