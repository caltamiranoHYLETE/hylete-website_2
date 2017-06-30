<?php
	class WindsorCircle_Export_Model_Status extends Mage_Core_Model_Abstract
	{
		protected function _construct(){
			$this->_init('windsorcircle_export/status');
		}

		/**
		 * Check status
		 * @param string $status
		 * @return string 'Y' - Yes or 'N' - No
		 */
		public function canceled($status){
			if($status == 'closed' || $status == 'canceled'){
				return 'Y';
			} else {
				return 'N';
			}
		}
	}