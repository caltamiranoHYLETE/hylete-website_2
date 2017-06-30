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

class Vaimo_Cms_Model_Wysiwyg_Editor extends Vaimo_Cms_Model_Editor_Abstract
{
    const UPDATE_ACTION = 'content_save';

    protected $_actions = array(
        self::UPDATE_ACTION => 'blockSave'
    );

    protected $_require = array(
        'updates'
    );

    public function _prepareContent($content)
    {
        $markupUpdater = $this->getFactory()->getSingleton('vaimo_cms/markup_updater');

        $converters = array(
            'convertRelativeUrlsToAbsolute',
            'removeEditModeParamsFromLinks',
            'convertImagesToMediaDirectives',
            'convertStoreUrlDirectives',
            'convertDirectUrlDirectives'
        );

        return array_reduce($converters, function($content, $converterMethod) use ($markupUpdater) {
            return $markupUpdater->$converterMethod($content);
        }, $content);
    }

    public function blockSave($arguments)
    {
        $factory = $this->getFactory();

        foreach ($arguments['updates'] as $update) {
            $block = $factory->getModel('cms/block')
                ->load($update['block_id']);

            $content = $this->_prepareContent($update['content']);

            $block->setContent($content)
                ->save();
        }
    }

    public function blockSaveResponse($arguments)
    {
        $response = array();
        $factory = $this->getFactory();

        foreach ($arguments['updates'] as $update) {
            $block = $factory->getModel('cms/block')
                ->load($update['block_id']);

            $response[] = array(
                'block_id' => $update['block_id'],
                'content' => $block->getContent()
            );
        }

        return array('updates' => $response);
    }
}