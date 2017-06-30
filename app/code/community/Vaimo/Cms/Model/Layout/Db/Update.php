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

class Vaimo_Cms_Model_Layout_Db_Update extends Vaimo_Cms_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('vaimo_cms/layout_db_update');
    }

    public function getDbUpdatesForPackageAndTheme($update, $package, $theme)
    {
        $updatesBefore = $update->asArray();

        if (!$updatesBefore) {
            $updatesBefore = array();
        }

        $storeDesignPackage = $this->getFactory()->getSingleton('core/design_package');

        $_configuration = $storeDesignPackage->setAllGetOld(array(
            'package' => $package
        ));

        $_theme = $storeDesignPackage->getTheme('layout');

        $storeDesignPackage->setTheme('layout', $theme);

        foreach ($update->getHandles() as $handle) {
            $update->fetchDbLayoutUpdates($handle);
        }

        $storeDesignPackage->setAllGetOld($_configuration);
        $storeDesignPackage->setTheme('layout', $_theme);

        $updatesAfter = $update->asArray();
        $newUpdates = $updatesBefore ? array_slice($updatesAfter, count($updatesBefore)) : $updatesAfter;

        $update->resetUpdates();

        foreach ($updatesBefore as $_update) {
            $update->addUpdate($_update);
        }

        return array_values(array_filter(array_unique($newUpdates)));
    }

    public function getDbUpdatesByNameInLayout($update)
    {
        $resource = $this->_getResource();
        $layoutHelper = $this->getFactory()->getHelper('vaimo_cms/layout');

        $result = $resource->getWidgetDbLayoutUpdatesForHandles($update->getHandles());

        $items = array();
        foreach ($result as $row) {
            $name = $layoutHelper->getNameInLayoutFromUpdateXml($row['xml']);

            if (!$name) {
                continue;
            }

            $data = array(
                'reference' => $row['block_reference'],
                'id' => $row['instance_id'],
                'name' => $name,
                'type' => $row['instance_type'],
                'page_id' => $row['page_id']
            );

            $items[$name] = $data;
        }

        return $items;
    }
}