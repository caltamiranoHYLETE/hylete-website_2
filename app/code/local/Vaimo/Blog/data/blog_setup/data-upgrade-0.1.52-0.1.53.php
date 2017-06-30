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
 * @package     Vaimo_Blog
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Ilya Voinov <ilya.voinov@vaimo.com>
 */

/** @var Vaimo_Blog_Model_Setup $this */
$this->startSetup();
$attributeSetName = Vaimo_Blog_Model_Setup::EAV_ATTR_SET;
$entityType = Mage_Catalog_Model_Product::ENTITY;
$attributeSetId = $this->getAttributeSetId($entityType, $attributeSetName);
$generalGroupId = $this->getAttributeGroupId($entityType, $attributeSetId, 'General');
if($attributeSetId) {
    $blogRequiredAttributes = array('visibility', 'name', 'status');
    foreach($blogRequiredAttributes as $attributeCode) {
        $attributeId = $this->getAttributeId($entityType, $attributeCode);
        $this->addAttributeToSet($entityType, $attributeSetId, $generalGroupId, $attributeId, 10);
    }
}
$this->endSetup();