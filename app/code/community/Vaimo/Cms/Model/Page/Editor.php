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

/**
 * Class Vaimo_Cms_Model_Page_Editor
 *
 * @method setTargetedStructures(array $structures)
 * @method array getTargetedStructures()
 */
class Vaimo_Cms_Model_Page_Editor extends Vaimo_Cms_Model_Editor_Abstract
{
    const PUBLISH_ACTION = 'page_publish';
    const DISCARD_ACTION = 'page_discard';

    protected $_actions = array(
        self::PUBLISH_ACTION => 'publishPage',
        self::DISCARD_ACTION => 'discardPage'
    );

    protected $_require = array(
        'revision'
    );

    public function always($arguments)
    {
        $factory = $this->getFactory();

        if (!Mage::getStoreConfigFlag(Vaimo_Cms_Helper_Data::XPATH_CONFIG_STAGING_ENABLED)) {
            $this->_invalidateCurrentPage();
        }

        return array(
            'page_draft' => (int)$factory->getHelper('vaimo_cms/page')
                ->hasDraft($this->_getPage(), $arguments['revision'])
        );
    }

    public function publishPage($arguments)
    {
        $structures = $this->getFactory()->getHelper('vaimo_cms/page')
            ->publish($this->_getPage(), $arguments['revision']);

        $this->setTargetedStructures($structures);

        $this->_invalidateCurrentPage();
    }

    public function discardPage($arguments)
    {
        $structureIds = $this->getFactory()->getHelper('vaimo_cms/page')
            ->discard($this->_getPage(), $arguments['revision']);

        $this->setTargetedStructures($structureIds);
    }

    public function getResponse()
    {
        $layout = $this->getApp()->getLayout();

        $structures = $this->getTargetedStructures();
        $structureHelper = $this->getFactory()->getHelper('vaimo_cms/structure');

        return array(
            'structures' => $structureHelper->getStructureOutputFromLayout($structures, $layout)
        );
    }

    protected function _invalidateCurrentPage()
    {
        $this->getFactory()->getHelper('vaimo_cms/cache')
            ->cleanTagsForPage(array($this->getCurrentLayoutHandle()));
    }

    protected function _getPage()
    {
        return $this->getFactory()->getModel('vaimo_cms/page', array(
            'handle' => $this->getCurrentLayoutHandle(),
            'store' => (int)$this->getStoreId()
        ));
    }
}