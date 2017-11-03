<?php
class Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');

        $collection = Mage::getResourceModel('cminds_coupon/coupon_collection')
            ->addRuleToFilter($priceRule)
            ->addGeneratedCouponsFilter()
            ->addCountedErrors();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => Mage::helper('salesrule')->__('Coupon Code'),
            'index'  => 'code'
        ));
        $this->addColumn('over_used', array(
            'header' => Mage::helper('cminds_coupon')->__('Used more times than it is defined'),
            'index'  => 'over_used',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('over_used_by_customer', array(
            'header' => Mage::helper('cminds_coupon')->__('Used more times than it is defined in customer group'),
            'index'  => 'over_used_by_customer',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('expired', array(
            'header' => Mage::helper('cminds_coupon')->__('Used when expired'),
            'index'  => 'expired',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('doesnt_match_conditions', array(
            'header' => Mage::helper('cminds_coupon')->__('Does not match conditions'),
            'index'  => 'doesnt_match_conditions',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('customer_not_assigned', array(
            'header' => Mage::helper('cminds_coupon')->__('Used in wrong customer group'),
            'index'  => 'customer_not_assigned',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('default_error', array(
            'header' => Mage::helper('cminds_coupon')->__('Default error message'),
            'index'  => 'default_error',
            'renderer' => 'Cminds_Coupon_Block_Promo_Quote_Edit_Tab_Coupons_Grid_Renderer_Errorvar',
            'filter' => false,
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('salesrule')->__('Created On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'align'  => 'center',
            'width'  => '160'
        ));
        $this->addColumn('used', array(
            'header'   => Mage::helper('salesrule')->__('Used'),
            'index'    => 'times_used',
            'width'    => '100',
            'type'     => 'options',
            'options'  => array(
                Mage::helper('adminhtml')->__('No'),
                Mage::helper('adminhtml')->__('Yes')
            ),
            'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
            'filter_condition_callback' => array(
                Mage::getResourceModel('salesrule/coupon_collection'), 'addIsUsedFilterCallback'
            )
        ));
        $this->addColumn('times_used', array(
            'header' => Mage::helper('salesrule')->__('Times Used'),
            'index'  => 'times_used',
            'width'  => '50',
            'type'   => 'number',
        ));

        $this->addExportType('*/*/exportCouponsCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportCouponsXml', Mage::helper('customer')->__('Excel XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('adminhtml')->__('Delete'),
            'url'  => $this->getUrl('*/*/couponsMassDelete', array('_current' => true)),
            'confirm' => Mage::helper('salesrule')->__('Are you sure you want to delete the selected coupon(s)?'),
            'complete' => 'refreshCouponCodesGrid'
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/couponsGrid', array('_current'=> true));
    }
}
