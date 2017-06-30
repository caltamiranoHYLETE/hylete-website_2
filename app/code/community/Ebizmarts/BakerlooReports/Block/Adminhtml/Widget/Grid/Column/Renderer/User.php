<?php

class Ebizmarts_BakerlooReports_Block_Adminhtml_Widget_Grid_Column_Renderer_User extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $result = parent::render($row);

        if($row->getAdminUser()) {
            $user = Mage::getModel('admin/user')->load($row->getAdminUser(), 'username');

            if($user->getId()) {
                $href = Mage::helper('adminhtml')->getUrl('adminhtml/permissions_user/edit', array('user_id' => $user->getId()));
                $result = '<a href="' . $href . '" target="_blank">' . $user->getName() . '</a>';
            }
            else
                $result = $row->getAdminUser();
        }
        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {
        $fullname = '';

        if($row->getAdminUser()) {
            $user = Mage::getModel('admin/user')->load($row->getAdminUser(), 'username');

            if ($user->getId())
                $fullname = $user->getName();
            else
                $fullname = $row->getAdminUser();
        }

        return $fullname;
    }
}