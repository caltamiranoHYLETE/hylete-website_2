<?php

class Ebizmarts_BakerlooReports_Adminhtml_Pos_ReportsController extends Mage_Adminhtml_Controller_Action {

    protected $dateFormat = 'Y:m:d H:i:s';

    public function indexAction(){

        $this->_title($this->__("Reports"));
        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }


    public function gridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_reports/adminhtml_pos_reports_grid')->toHtml()
        );
    }

    public function deleteAction(){
        $id = $this->getRequest()->getParam('id');
        $report = Mage::helper('bakerloo_reports')->loadReport($id);

        try {
            if (Mage::registry('bakerloo_reports_current') == $report)
                Mage::unregister('bakerloo_reports_current');

            if(!$report->getId())
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('bakerloo_reports')->__("The report doesn't exist anymore.")
                );

//            if (Mage::helper('bakerloo_reports')->isDefaultReport($report))
//                Mage::getSingleton('adminhtml/session')->addError(
//                    Mage::helper('bakerloo_reports')->__("The report can't be deleted.")
//                );
//            else {
//                $report->drop();
//                $report->delete();
//
//
//                Mage::getSingleton('adminhtml/session')->addSuccess(
//                    Mage::helper('bakerloo_reports')->__("Report deleted successfully.")
//                );
//            }

            $report->drop();
            $report->delete();


            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('bakerloo_reports')->__("Report deleted successfully.")
            );
            $this->_redirectReferer();
        }
        catch(Exception $ex){

//            Mage::log($ex->getMessage(), null, 'BakerlooReports.log', true);

            Mage::logException($ex);

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Report delete failed. " . $ex->getMessage())
            );
        }
    }

    public function updateAction(){
        $id = $this->getRequest()->getParam('id');
        $report = Mage::helper('bakerloo_reports')->loadReport($id);

        if(!$report->getId()){
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Report doesn't exist anymore. ")
            );
            return;
        }
        try {
            $writer = Mage::getSingleton('core/resource')->getConnection('core_write');
            $report->populate($writer);

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('bakerloo_reports')->__("Report updated successfully.")
            );
            $this->_redirectReferer();
        }
        catch(Exception $ex){

//            Mage::log($ex->getMessage(), null, 'BakerlooReports.log', true);

            Mage::logException($ex);

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('bakerloo_reports')->__("Report update failed. " . $ex->getMessage())
            );

            $this->_redirectReferer();
        }
    }

    public function newAction(){
        $this->_forward('edit');
    }

    public function editAction(){
        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();
    }

    public function saveAction(){

        if($this->getRequest()->isPost()) {
            $h = Mage::helper('bakerloo_reports');

            try {
                $data = $this->getRequest()->getPost();
                $data = $data['report'];
                
                $reportName = $data['report_name'];
                $selectedColumns = $data['columns'];
                $from = $data['from'];
                $to = $data['to'];

                list($columnDefinitions, $dataSources) = $h->getReportConfig($selectedColumns);

                $filters = array();
                if($from) {
                    $from = date($this->dateFormat, strtotime($from, time()));
                    $filters[] = array('created_at', array('gteq' => $from));
                }
                if($to) {
                    $to = date($this->dateFormat, strtotime($to, time()));
                    $filters[] = array('created_at', array('lteq' => $to));
                }

                $generator = Mage::getModel('bakerloo_reports/generator');

                $writer = $generator->getWriter();
                $generator->generate($reportName, $columnDefinitions, $dataSources, $filters, $writer);

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $h->__("Report saved successfully.")
                );

                $this->_redirect('*/*');
                return;
            }
            catch(Exception $e){

//                Mage::log($e->getMessage(), null, 'BakerlooReports.log', true);

                Mage::logException($e);

                Mage::getSingleton('adminhtml/session')->addError(
                    $h->__("Report could not be saved. " . $e->getMessage())
                );

                $this->_redirectReferer();
            }
        }
    }

}