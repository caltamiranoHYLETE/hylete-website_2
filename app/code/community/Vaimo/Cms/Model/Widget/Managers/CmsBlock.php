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

class Vaimo_Cms_Model_Widget_Managers_CmsBlock extends Vaimo_Cms_Model_Editor_Abstract
    implements Vaimo_Cms_Model_Widget_ManagerInterface
{
    protected $clonedBlocks = array();

    public function generateParams($handle, $reference)
    {
        $factory = $this->getFactory();
        $cmsBlock = $factory->getModel('cms/block');

        $cmsBlock->addData(array(
            'title' => $factory->getHelper('vaimo_cms')->__("Block for '%s' in '%s' column", $handle, $reference),
            'identifier' => $handle . '_'. md5(time() *  rand(1, time())),
            'content' => $factory->getHelper('vaimo_cms')->__('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor' .
                'incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud' .
                'exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat</p>'),
            'is_active' => 1,
            'stores' => array(0, $this->getApp()->getStore()->getId())
        ));

        $cmsBlock->save();

        return array(
            'block_id' => $cmsBlock->getId()
        );
    }

    public function createParams(array $parameters)
    {
        if (isset($parameters['_vcms_block_id'])) {
            return array();
        }

        if (!isset($parameters['_vcms_user_configured'])) {
            return array();
        }

        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_STAGING_ENABLED)) {
            return array();
        }

        return $this->cloneParams($parameters);
    }

    public function cloneParams(array $parameters)
    {
        $factory = $this->getFactory();

        $sourceId = $parameters['block_id'];

        if (!isset($this->clonedBlocks[$sourceId])) {
            $cmsBlock = $factory->getModel('cms/block')
                ->load($parameters['block_id']);

            $identifier = $cmsBlock->getIdentifier();

            if (!$identifierPrefix = substr($identifier, 0, strrpos($identifier, '_'))) {
                $identifierPrefix = $identifier;
            }

            $cmsBlock->unsBlockId()
                ->setIdentifier($identifierPrefix . '_' . $factory->getHelper('core')->uniqHash())
                ->setTitle($factory->getHelper('vaimo_cms')->__("Cloned copy of cms block id %d", $sourceId))
                ->setStores(array(0));

            $cmsBlock->save();

            $this->clonedBlocks[$sourceId] = $cmsBlock->getId();
        }

        return array(
            'block_id' => $this->clonedBlocks[$sourceId],
            '_vcms_block_id' => $sourceId
        );
    }

    public function publishParams(array $parameters)
    {
        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_REUSE_CMS_BLOCKS)) {
            return array();
        }

        if (!isset($parameters['_vcms_user_configured'])) {
            return array();
        }

        $factory = $this->getFactory();

        if (!isset($parameters['_vcms_block_id'], $parameters['block_id'])) {
            return array();
        }

        if ($parameters['_vcms_block_id'] == $parameters['block_id']) {
            return array();
        }

        $originBlock = $factory->getModel('cms/block')
            ->load($parameters['_vcms_block_id']);

        if (!$originBlock->hasData()) {
            return array();
        }

        $block = $factory->getModel('cms/block')
            ->load($parameters['block_id']);

        if ($block->getContent() != $originBlock->getContent()) {
            $originBlock->setContent($block->getContent());
            $originBlock->save();
        }

        $block->delete();

        return array(
            'block_id' => $originBlock->getId(),
            '_vcms_block_id' => false
        );
    }
}
