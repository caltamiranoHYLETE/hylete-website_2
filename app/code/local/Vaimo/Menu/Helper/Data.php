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

class Vaimo_Menu_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_ROOT_LEVEL = 2;

    protected $_categoryModel;

    protected function _getCategoryModel()
    {
        if (!$this->_categoryModel) {
            $this->_categoryModel = Mage::getModel('catalog/category');
        }

        return $this->_categoryModel;
    }

    public function getCategoryProducts($categoryId, $page, $limit, $sort = 'position')
    {
        /**
         * Needs to be loaded due to addCategoryFilter needing a bit more than just a category_id
         */
        $category = $this->_getCategoryModel()->load($categoryId);
        $storeId = Mage::app()->getStore()->getId();

        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSort($sort, 'ASC')
            ->addAttributeToSelect('url_key')
            ->addCategoryFilter($category)
            ->addAttributeToSelect('name')
            ->setStoreId($storeId)
            ->setPage($page, $limit);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);

        return $products;
    }

    public function getTopMenuType()
    {
        return Mage::getStoreConfig(Vaimo_Menu_Model_Type::XPATH_CONFIG_SELECTED_MENU_TYPE);
    }

    public function getCategoryImageUrl($menuItem)
    {
        if (!($imageName = $menuItem->getMenuImage())) {
            $imageName = $menuItem->getImage();
        }

        if ($imageName) {
            return $this->_getCategoryModel()->setImage($imageName)->getImageUrl();
        }

        return '';
    }

    public function getConfiguredDesignInformationForStore($storeId)
    {
        $defaultStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $storeId = $storeId ? $storeId : $defaultStoreId;
        $design = Mage::getModel('core/design_package')->setArea('frontend');
        $design->setStore($storeId);
        $package = $design->getPackageName('frontend');
        $theme = $design->getTheme('frontend');
        $packageTheme = $package . '/' . $theme;

        return new Varien_Object(array(
            'package' => $package,
            'theme' => $theme,
            'store_id' => $storeId,
            'package_theme' => ($packageTheme == '/' ? null : $packageTheme)
        ));
    }

    public function getBaseDesignPackage()
    {
        return Mage_Core_Model_Design_Package::BASE_PACKAGE;
    }

    public function getBaseDesignTheme()
    {
        return Mage_Core_Model_Design_Package::DEFAULT_THEME;
    }

    public function getBasePackageTheme()
    {
        return $this->getBaseDesignPackage() . '/' . $this->getBaseDesignTheme();
    }

    public function getDecoratorWithPredefinedMap()
    {
        $map = array(
            'column_breakpoint' => 'column-break-marker',
            'include_in_menu' => 'in-menu-marker',
            'menu_group' => array()
        );

        foreach (Mage::getModel('vaimo_menu/group')->getAll() as $code => $label) {
            $color = null;

            if ($code != Vaimo_Menu_Model_Group::DEFAULT_GROUP) {
                $color = Vaimo_Menu_Color::generateColorFromSeed($label);
            }

            $map['menu_group'][$code] = array('backgroundColor' => $color);
        }

        $decorator = Mage::getModel('vaimo_menu/adminhtml_category_tree_decorator');

        return $decorator->setMap($map);
    }

    public function getSessionIdUsedInUrl()
    {
        return Mage::app()->getUseSessionVar() || Mage::app()->getUseSessionInUrl();
    }
}