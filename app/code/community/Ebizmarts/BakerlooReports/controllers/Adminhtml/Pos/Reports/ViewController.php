<?php


class Ebizmarts_BakerlooReports_Adminhtml_Pos_Reports_ViewController extends Mage_Adminhtml_Controller_Action {

    public function indexAction(){

        $id = $this->getRequest()->getParam('id');
        $report = Mage::helper('bakerloo_reports')->loadReport($id);

        if(!$report->getId())
            $this->_redirectReferer();

        Mage::register('bakerloo_reports_current', $report);

        $this->_title($this->__("POS Orders Report"));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction(){

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_reports/adminhtml_pos_reports_view_grid')->toHtml()
        );
    }

    public function deleteAction(){
        try {
            $id = $this->getRequest()->getParam('id');
            $rowId = $this->getRequest()->getParam('row_id');
            $report = Mage::helper('bakerloo_reports')->loadReport($id);

            if(!$report->getId())
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bakerloo_reports')->__("The report doesn't exist anymore.")
                );

            $report->deleteRow($rowId);

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('bakerloo_reports')->__("Report deleted successfully.")
            );

        }
        catch(Exception $ex){

//            Mage::log($ex->getMessage(), null, 'BakerlooReports.log', true);

            Mage::logException($ex);

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Could not delete row. " . $ex->getMessage())
            );
        }

        $this->_redirectReferer();
    }

    /**
     * Export data to CSV format
     */
    public function exportCsvAction() {

        $id = $this->getRequest()->getParam('id');
        $report = Mage::helper('bakerloo_reports')->loadReport($id);

        if(!$report->getId()) {
            $this->_redirectReferer();
        }

        Mage::register('bakerloo_reports_current', $report);

        $filter = $this->getRequest()->getParam('filter');
        $filter_data = Mage::helper('adminhtml')->prepareFilterString($filter);
        $report->setFilters($filter_data);

        $fileName = $report->getFileName() . '.csv';
        $content = $report->getCsvFile($fileName, $filter_data);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Regenerate current report
     */
    public function regenerateAction() {

        $id = $this->getRequest()->getParam('report_id');
        $report = Mage::helper('bakerloo_reports')->loadReport($id);

        if(!$report->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("The report doesn't exist anymore.")
            );

            $this->_redirectReferer();
        }

        try {
            $report->regenerate();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('bakerloo_reports')->__("Report regenerated successfully.")
            );

            Mage::register('bakerloo_reports_current', $report);

            $this->_redirectReferer();
        }
        catch(Exception $ex) {

            Mage::logException($ex);

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Report generation failed! {$ex->getMessage()}")
            );

            $this->_redirectReferer();
        }
    }
}