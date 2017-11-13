<?php
class Globale_BrowsingLite_TrackingController extends Mage_Core_Controller_Front_Action {


	/**
	 *  Show Tracking script in template according to setting
	 */
	public function getAction(){
		$TrackingJs = Mage::getModel('globale_base/settings')->getTrackingJs();

		$TrackingData = str_replace('%TEXTAREA%',$TrackingJs,$this->getTrackingTemplate());

		$this->getResponse()
			->clearHeaders()
			->setHeader('Content-Type', 'application/javascript')
			->setHeader('Cache-Control', 'max-age=3600')
			->setBody($TrackingData);

	}


	/**
	 * Get tracking script template
	 * @return string
	 */
	protected function getTrackingTemplate()
	{
		$Template = 'var glegem = glegem || function() {
    (window["glegem"].q = window["glegem"].q || []).push(arguments)
};
glegem("OnCheckoutStepLoaded", function(data) {
    switch (data.StepId) {
        case data.Steps.LOADED:
        case data.Steps.CONFIRMATION:
            if (data.IsSuccess) { 
            %TEXTAREA%
            }
    }
});';
		return $Template;
	}

}