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

class Vaimo_Cms_Model_Store extends Vaimo_Cms_Model_Abstract
{
    protected $_requiredArguments = array('store');
    protected $_storeId;

    protected $_pages = false;
    protected $_structures = array();
    protected $_loaded = false;

    public $pageMocks = array();

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);

        $this->_storeId = $required['store'];

        parent::__construct($args);
    }

    protected function _construct($parameters = array())
    {
        $this->_init('vaimo_cms/store');
    }

    protected function _loadStructures()
    {
        if (!$this->_loaded) {
            $resource = $this->getResource();
            $this->_structures = $resource->getStructuresCollectionForStore($this->_storeId);
            $this->_loaded = true;
        }

        return $this->_structures;
    }

    public function getPages()
    {
        $structuresByHandle = array();

        $factory = $this->getFactory();

        if ($this->_pages === false) {
            $pages = array();

            foreach ($this->getStructures() as $structure) {
                $handle = $structure->getHandle();

                if (!isset($structuresByHandle[$handle])) {
                    $structuresByHandle[$handle] = array();
                }

                $structuresByHandle[$handle][] = $structure;
            }

            foreach ($structuresByHandle as $handle => $structures) {
                $pages[] = $factory->getModel('vaimo_cms/revision', array(
                    'handle' => $handle,
                    'store' => $this->_storeId,
                    'structures' => $structures
                ));
            }

            $this->_pages = $pages;
        }

        return $this->_pages;
    }

    public function getStructures()
    {
        return $this->_loadStructures();
    }

    public function save()
    {
        $pages = $this->getPages();

        foreach ($pages as $page) {
            $page->save();
        }
    }
}