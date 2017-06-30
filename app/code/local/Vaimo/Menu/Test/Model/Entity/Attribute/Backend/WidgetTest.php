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

class Vaimo_Menu_Test_Model_Entity_Attribute_Backend_WidgetTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;
    protected $_entity;
    protected $_widget;
    protected $_widgetConfiguration = array(
        'widget_type' => 'test/widget',
        'parameters' => array()
    );

    public $layoutBlocks = array();

    protected function _setupLayoutAnalyserMock()
    {
        $object = $this;
        $analyser = $this->getMock('Vaimo_Menu_Model_Layout_Analyser');
        $analyser->expects($this->any())
            ->method('getWidgetContainersForHandle')
            ->will($this->returnCallback(
                function() use ($object) {
                    return $object->layoutBlocks;
                }
            ));

        return $analyser;
    }

    protected function _setupWidgetMock()
    {
        $widget = $this->getMock('Mage_Widget_Model_Widget_Instance', array('save'));
        $widget->expects($this->any())
            ->method('save')
            ->will($this->returnCallback(
                function() use ($widget) {
                    $widget->setInstanceId(123);
                    return $widget;
                }
            ));

        $this->_widget = $widget;
        return $this->_widget;
    }

    public function setUp()
    {
        Mage::app()->cleanCache(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);

        $this->layoutBlocks = array(
            'block1' => new Varien_Simplexml_Element('<block1><label attributes="test_widget"></label></block1>')
        );

        $mockedModels = array(
            'widget/widget_instance' => $this->_setupWidgetMock(),
            'vaimo_menu/layout_analyser' => $this->_setupLayoutAnalyserMock()
        );

        $factory = $this->_getFactoryWithModelMocks($mockedModels);

        $this->_model = Mage::getModel('vaimo_menu/entity_attribute_backend_widget', array('factory' => $factory));
        $attribute = new Varien_Object(array('attribute_code' => 'test_widget'));
        $this->_model->setAttribute($attribute);

        $this->_entity = new Varien_Object(array(
            'store_id' => 0,
            'test_widget' => $this->_widgetConfiguration
        ));
    }

    public function testBeforeSaveShouldCreateWidgetInstancePageGroupForAllWidgetAttributeReferencesUsedInLayout()
    {
        $this->_setupCacheTest(Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG);

        $this->layoutBlocks = array(
            'block1' => new Varien_Simplexml_Element('<block1><label attributes="test_widget"></label></block1>'),
            'block2' => new Varien_Simplexml_Element('<block2><label attributes="other_widget"></label></block2>'),
            'block3' => new Varien_Simplexml_Element('<block3><label attributes="test_widget"></label></block3>'),
            'block4' => new Varien_Simplexml_Element('<block4><label attributes="third_widget"></label></block4>'),
        );

        $this->_entity->setTestWidget($this->_widgetConfiguration);

        $expected = array(
            array(
                'page_group' => 'all_pages',
                'all_pages' => array(
                    'page_id' => 0,
                    'layout_handle' => 'default',
                    'for' => 'all',
                    'block' => 'block1',
                    'template' => null
                )
            ),
            array(
                'page_group' => 'all_pages',
                'all_pages' => array(
                    'page_id' => 0,
                    'layout_handle' => 'default',
                    'for' => 'all',
                    'block' => 'block3',
                    'template' => null
                )
            )
        );

        $this->_model->beforeSave($this->_entity);
        $pageGroups = $this->_widget->getPageGroups();

        $this->assertEquals($expected, $pageGroups);
    }

    public function _setupDesignPackageMock()
    {
        $designPackageMock = $this->getMock('Mage_Core_Model_Design_Package', array('designPackageExists'));
        $designPackageMock->expects($this->any())
            ->method('designPackageExists')
            ->will($this->returnValue(true));
        $this->_mockModelSingleton('core/design_package', $designPackageMock);

        return $designPackageMock;
    }

    public function testBeforeSaveShouldCreateBlockInstanceForBaseDefaultThemeEvenWhenStorePackageIsDifferent()
    {
        $this->_setupDesignPackageMock()
            ->setAllGetOld(array('package' => 'testpackage'));

        $this->_model->beforeSave($this->_entity);

        $this->assertEquals('base/default', $this->_widget->getPackageTheme());
    }

    /**
     * @expectedException       Vaimo_Menu_Exception
     * @expectedExceptionCode   Vaimo_Menu_Exception::LAYOUT_REFERENCES_NOT_FOUND
     */
    public function testBeforeSaveShouldRaiseAnExceptionWithTheAttributeIsNotReferencesInLayout()
    {
        $this->layoutBlocks = array();

        $this->_model->beforeSave($this->_entity);
    }

    public function testBeforeSaveShouldCreateWidgetInstanceEvenWhenWidgetDataIsSerialized()
    {
        $this->_entity->setTestWidget('widget_type=test%2Fwidget&parameters%5Btest1%5D=1&parameters%5Btest2%5D=2');

        $this->_model->beforeSave($this->_entity);

        $this->assertEquals('test/widget', $this->_widget->getType());
        $this->assertEquals(array('test1' => 1, 'test2' => 2), $this->_widget->getWidgetParameters());
    }

    public function testBeforeSaveShouldAddCreatedWidgetInstanceIdAsAttributeValue()
    {
        $this->_model->beforeSave($this->_entity);

        $this->assertEquals(123, $this->_widget->getInstanceId());
    }

    public function testBeforeSaveShouldMakeCreatedWidgetInheritStoreIdFromObject()
    {
        $this->_entity->setStoreId(888);

        $this->_model->beforeSave($this->_entity);

        $this->assertEquals(array(888), $this->_widget->getStoreIds());
    }
}