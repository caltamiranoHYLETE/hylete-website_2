<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_CMSDISPLAYRULES
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

 

class Itoris_CmsDisplayRules_Model_Observer {

	protected $rewriteBlockIds = array();

	/**
	 * Save alternate content for cms page or block
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function saveCms(Varien_Event_Observer $observer) {
		$modelObject = $observer->getObject();
		$id = (int)$modelObject->getId();
		if ($id) {
			$data = Mage::app()->getRequest()->getParam('itoris_cms_display_rules');
			if (is_array($data)) {
				if ($modelObject instanceof Mage_Cms_Model_Page) {
					$cmsModel = Mage::getModel('itoris_cmsdisplayrules/page');
					$cmsModel->setPageId($id);
					$cmsMode = 'page';
				} elseif ($modelObject instanceof Mage_Cms_Model_Block) {
					$cmsModel = Mage::getModel('itoris_cmsdisplayrules/block');
					$cmsModel->setBlockId($id);
					$cmsMode = 'block';
				}
				if (isset($cmsModel)) {
					if (!empty($data['ending']) && !empty($data['starting'])) {
						$start = $this->getDataHelper()->getDate($data['starting']);
						$end = $this->getDataHelper()->getDate($data['ending']);
						if ($end->compareDate($start) !== -1) {
							$cmsModel->setStartDate($data['starting']);
							$cmsModel->setFinishDate($data['ending']);
							$cmsModel->setAnotherCms($data['another_cms']);
							$cmsModel->save();
						} else {
							Mage::getSingleton('core/session')->addError('Ending on date must be greater than starting on date');
							return;
						}
					} else {
						$cmsModel->setStartDate($data['starting']);
						$cmsModel->setFinishDate($data['ending']);
						$cmsModel->setAnotherCms($data['another_cms']);
						$cmsModel->save();
					}
					$resource = Mage::getSingleton('core/resource');
					$connection = $resource->getConnection('read');
					if ($cmsMode == 'page') {
						$tableGroup = $resource->getTableName('itoris_cms_display_rules_page_group');
						$valueUserGroup = $data['groups'];
						$connection->query("delete from {$tableGroup} where page_id={$id}");
						foreach ($valueUserGroup as $group) {
							if ($group != 'all') {
								$connection->query("insert into {$tableGroup} (page_id, group_id) values ({$id}, {$group})");
							}
						}
					} elseif ($cmsMode == 'block') {
						$tableGroup = $resource->getTableName('itoris_cms_display_rules_block_group');
						$valueUserGroup = $data['groups'];
						$connection->query("delete from {$tableGroup} where block_id={$id}");
						foreach ($valueUserGroup as $group) {
							if ($group != 'all') {
								$connection->query("insert into {$tableGroup} (block_id, group_id) values ({$id}, {$group})");
							}
						}
					}
				}
			}
		}
	}

	public function displayCms(Varien_Event_Observer $observer) {
		$model = $observer->getObject();
        if (!Mage::app()->getStore()->isAdmin()) {
            if ($model instanceof Mage_Cms_Model_Page) {
                if ($this->getDataHelper()->isRegisteredFrontend()) {
                    $id = (int)$model->getId();

                    $customModel = Mage::getModel('itoris_cmsdisplayrules/page')->load($id);
                    $cmsModel = Mage::getModel('cms/page')->getCollection();
                    $idType = 'page_id';
                }
            } elseif ($model instanceof Mage_Cms_Model_Block) {
                if ($this->getDataHelper()->isRegisteredFrontend()) {
                    $id = (int)$model->getId();
                    $customModel = Mage::getModel('itoris_cmsdisplayrules/block')->load($id);
                    $cmsModel = Mage::getModel('cms/block');
                    $idType = 'block_id';
                }
            }
            if (isset($customModel)) {
                if ($this->correctSetting($customModel)) {
                    $idOtherCms = (int)$customModel->getAnotherCms();
                    if ($idOtherCms != 0) {
                        if ($idType == 'page_id') {
                            $cmsModel->addFieldToFilter($idType, array('eq' => $idOtherCms));
                            foreach ($cmsModel as $curModel) {
                                $redirectUrl = Mage::getUrl($curModel->getIdentifier());
                            }
                            Mage::app()->getResponse()->setRedirect("$redirectUrl");
                        } elseif ($idType == 'block_id') {
                            if (!in_array($idOtherCms, $this->rewriteBlockIds)) {
                                $this->rewriteBlockIds[] = $idOtherCms;
                                $cmsModel->load($idOtherCms);
                                $model->setContent($cmsModel->getIsActive() ? $cmsModel->getContent() : "");
                            } else {
                                $model->setContent('');
                            }
                            $this->rewriteBlockIds = array();
                        }
                    } else {
                        if ($idType == 'page_id') {
                            $model->setId(null);
                        } elseif ($idType == 'block_id') {
                            $model->setContent('');
                        }
                    }
                }
            }
        }

	}

	protected function correctSetting($cmsModel) {
		if (is_null($this->getDataHelper()->customerGroup($cmsModel->getGroupId()))
            && $this->getDataHelper()->isVisibleByRestrictionDate($cmsModel->getStartDate(), $cmsModel->getFinishDate())
        ) {
            return true;
        } else if (is_null($this->getDataHelper()->customerGroup($cmsModel->getGroupId()))
            && !$this->getDataHelper()->isVisibleByRestrictionDate($cmsModel->getStartDate(), $cmsModel->getFinishDate()))
        {
            return false;
        } elseif (!$this->getDataHelper()->customerGroup($cmsModel->getGroupId())
            && !$this->getDataHelper()->isVisibleByRestrictionDate($cmsModel->getStartDate(), $cmsModel->getFinishDate()))
        {
            return true;
        }
        return false;
	}

	/**
	 * @return Itoris_CmsDisplayRules_Helper_Data
	 */

	public function getDataHelper() {
		return Mage::helper('itoris_cmsdisplayrules');
	}
}
?>