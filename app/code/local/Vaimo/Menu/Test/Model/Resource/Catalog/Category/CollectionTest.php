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
 * @comment     This collection is only needed to make sure default values work the same way that they're implemented in: flat, getModel()->load()
 */

class Vaimo_Menu_Test_Model_Resource_Catalog_Category_CollectionTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_pdoResponse;
    protected $_sqlFetchEntities = "SELECT e.* FROM catalog_category_entity AS e WHERE (e.entity_type_id = '3')";
    protected $_sqlFetchAttributes = "SELECT t_v.store_id, t_v.attribute_id, t_v.entity_id, {?} AS has_store_value, {?} AS default_value, {?} AS store_value FROM catalog_category_entity_varchar AS t_v WHERE (t_v.entity_type_id = 3) AND (t_v.entity_id IN (1)) AND (t_v.attribute_id IN ({?})) GROUP BY t_v.entity_id, t_v.attribute_id HAVING (t_v.store_id IN (0, 1))";

    protected function _setupPdoMock()
    {
        $settings = array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => 'test2',
            'dbname' => 'test',
        );

        $pdoMock = $this->getMock('Mock_Pdo', array('isConnected','fetchAll'), array($settings));
        $queryResponse = &$this->_pdoResponse;
        $pdoMock->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnCallback(
                function($query) use (&$queryResponse) {
                    $_query = str_replace(array('`', ' ',"\t", "\n", '*'), array('', '', '', '', '#'), $query);
                    foreach ($queryResponse as $pattern => $response) {
                        $_pattern = str_replace(array('`', ' ', "\t","\n", '*', '{?}'), array('', '','', '', '#', '*'), $pattern);

                        if (fnmatch($_pattern, $_query)) {
                            return $response;
                        }
                    }

                    return array();
                }
            ));

        $pdoMock->expects($this->any())
            ->method('isConnected')
            ->will($this->returnValue(true));

        return $pdoMock;
    }

    public function setUp()
    {
        $this->_queryResponse = array();
        $this->_setUpResourceMockWithMockedPdo();

        $this->_model = Mage::getResourceModel('vaimo_menu/catalog_category_collection');
        $this->_model->addAttributeToSelect(array('name'));
        $this->_model->setStoreId(1);
    }

    protected function _setUpResourceMockWithMockedPdo()
    {
        $connection = $this->_setupPdoMock();
        $resource = $this->getMock('Mage_Catalog_Model_Resource_Category', array('getReadConnection'));
        $resource->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($connection));

        $this->_mockResourceSingleton('catalog/category', $resource);

        $this->_pdoResponse[$this->_sqlFetchEntities] = array(
            array('entity_id' => 1)
        );
    }

    public function testLoadShouldSetAttributeValueToStoreBasedValue()
    {
        $expected = array('1' => array('entity_id' => 1, 'name' => 'name2'));
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->_model->getEntity()->getType(), 'name');

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array(
                array('entity_id' => 1, 'attribute_id' => $attribute->getAttributeId(), 'store_value' => 'name2', 'default_value' => 'name1')
        );

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }

    public function testLoadShouldSetAttributeValueToStoreBasedValueEvenWhenItsNullIfHasStoreValueFlagEvaluatesAsTrue()
    {
        $expected = array('1' => array('entity_id' => 1, 'name' => null));
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->_model->getEntity()->getType(), 'name');

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array(
            array('entity_id' => 1, 'attribute_id' => $attribute->getAttributeId(), 'has_store_value' => 1, 'store_value' => null, 'default_value' => 'name1')
        );

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }

    public function testLoadShouldSetAttributeValueToDefaultValueWhenStoreBasedValueIsNullAndHasStoreValueFlagEvaluatesAsFalse()
    {
        $expected = array('1' => array('entity_id' => 1, 'name' => 'name1'));
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->_model->getEntity()->getType(), 'name');

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array(
            array('entity_id' => 1, 'attribute_id' => $attribute->getAttributeId(), 'has_store_value' => null, 'store_value' => null, 'default_value' => 'name1')
        );

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }

    public function testLoadShouldSetAttributeValueToStoreBasedValueEvenIfDefaultIsMissing()
    {
        $expected = array('1' => array('entity_id' => 1, 'name' => 'name2'));
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->_model->getEntity()->getType(), 'name');

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array(
            array('entity_id' => 1, 'attribute_id' => $attribute->getAttributeId(), 'store_value' => 'name2')
        );

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }

    public function testLoadShouldSetAttributeValueToDefaultValueIfStoreValueIsMissing()
    {
        $expected = array('1' => array('entity_id' => 1, 'name' => 'name1'));
        $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($this->_model->getEntity()->getType(), 'name');

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array(
            array('entity_id' => 1, 'attribute_id' => $attribute->getAttributeId(), 'default_value' => 'name1')
        );

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }

    public function testLoadShouldNotSetAttributeValueIfNoValuesAreFound()
    {
        $expected = array('1' => array('entity_id' => 1));

        $this->_pdoResponse[$this->_sqlFetchAttributes] = array();

        $this->_model->load();

        $this->assertEquals($expected, $this->_model->toArray());
    }
}

class Mock_Pdo extends Varien_Db_Adapter_Pdo_Mysql
{
    public function _connect()
    {
        if (!$this->_connection) {
            $this->_connection = new PDO('sqlite::memory:');
        }
    }
}