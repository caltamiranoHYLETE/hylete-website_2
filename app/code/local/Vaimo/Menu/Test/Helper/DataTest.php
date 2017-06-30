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

class Vaimo_Menu_Test_Helper_DataTest extends Vaimo_Menu_Test_BaseCase
{
    protected $_model;

    public function setUp()
    {
        parent::setUp();

        $this->_model = new Vaimo_Menu_Helper_Data();
    }

    public function testGetCategoryImageUrlShouldReturnFullUrlOfMenuItemImage()
    {
        $expected = $imageName = 'image_test.jpg';
        $menuItem = new Varien_Object(array('image' => $imageName));

        $result = $this->_model->getCategoryImageUrl($menuItem);

        $this->assertContains($expected, $result);
    }

    public function testGetCategoryImageUrlShouldPreferMenuImageParameterIfItIsSet()
    {
        $imageName = 'image_test.jpg';
        $expected = $menuImage = 'test_menu_image.jpg';
        $menuItem = new Varien_Object(array('image' => $imageName, 'menu_image' => $menuImage));

        $result = $this->_model->getCategoryImageUrl($menuItem);

        $this->assertContains($expected, $result);
    }
}