<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
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
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_Model_Menu extends Vaimo_AppApi_Model_Abstract
{

    /**
     * First level of categories include appropriate level of detail, all details levels require a category load
     * Having detailed level on all levels became way to slow, so I only do the detail selection on the first level
     *
     * @param $categories
     *
     * @return array
     */
    protected function _prepareCategories($categories, $websiteId, $storeId)
    {
        $res = array();

        foreach ($categories as $category) {

            if (isset($category['is_active']) && $category['is_active']==0) {
                continue;
            }

            $catObj = $this->_getHelper()->getCategoryMenuDetails($category, $this->_getHelper()->getStoreId($websiteId, $storeId));

            if (!$catObj) {
                continue;
            }

            $catArr = $this->_getHelper()->dispatchUpdateEventObject( 'app_api_list_menu_record', $catObj, array('category' => $category) );

            $res[] = $catArr;
        }

        return $res;
    }
    protected function _convertObjectsToArrays($incomingObj)
    {
        if (is_object($incomingObj)) {
            $localObj = (array)$incomingObj->getData();
        } else {
            $localObj = $incomingObj;
        }
        if (is_array($localObj)) {
            $new = array();
            foreach ($localObj as $key => $val) {
                $new[$key] = $this->_convertObjectsToArrays($val);
            }
        } else {
            $new = $localObj;
        }
        return $new;
    }

    /**
     * First level of categories include appropriate level of detail, all details levels require a category load
     * Having detailed level on all levels became way to slow, so I only do the detail selection on the first level
     *
     * @param $categories
     *
     * @return array
     */
    protected function _prepareVaimoMenues($menus, $storeId)
    {
        $res = array();

        foreach ($menus as $item) {
            foreach ($item->getChildren() as $childMenu) {

                $category = $childMenu->getData();
                $category['category_id'] = $childMenu->getEntityId();
                $catObj = $this->_getHelper()->getCategoryDetails($category, $storeId, Vaimo_AppApi_Helper_Data::APP_API_DETAIL_LEVEL_SMART);

                $children = $catObj->getChildren();
                $childrenArr = $this->_convertObjectsToArrays($children);
                $catObj->setChildren($childrenArr);

                if (!$catObj) {
                    continue;
                }

                $catArr = $this->_getHelper()->dispatchUpdateEventObject('app_api_list_menu_record', $catObj, array('category' => $category));

                $res[] = $catArr;
            }
        }

        return $res;
    }

    public function listMenues($websiteId, $storeId, $treeFlag, $useVaimoMenu)
    {
        if ($useVaimoMenu) {
            $storeId = $this->_getHelper()->getStoreId($websiteId, $storeId);
            $categories = Mage::getModel('vaimo_menu/catalog_category_tree')->
                getCategoryTree(array(), null, $storeId);
            $categoryDetails = $this->_prepareVaimoMenues($categories, $storeId);
        } else {
            if ($treeFlag) {
                $categories = $this->_getHelper()->getCategoryTree($websiteId, $storeId);
            } else {
                $categories = $this->_getHelper()->getCategories($websiteId, $storeId);
            }
            $categoryDetails = $this->_prepareCategories($categories, $websiteId, $storeId);
        }

        $res = $this->_getHelper()->dispatchUpdateEventArray( 'app_api_list_menu', $categoryDetails, array('categories' => $categories) );

        return $res;
    }

}
