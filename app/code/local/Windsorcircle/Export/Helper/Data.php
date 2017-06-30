<?php
	class Windsorcircle_Export_Helper_Data extends Mage_Core_Helper_Abstract
	{
        public function getExtensionVersion()
        {
            return (string) Mage::getConfig()->getModuleConfig('Windsorcircle_Export')->version;
        }
        /**
         * Replace all tabs with spaces and replace all new lines with html <br />
         *
         * @param   $string
         * @param   int $tabspaces
         * @return  mixed
         */
        public function formatString($string, $tabspaces = 4) {
			$string = str_replace(array('\t', "\t"), str_repeat(" ",$tabspaces), $string);
			// use str_replace instead of nl2br because nl2br inserts html line breaks before all newlines but does not replace newlines
			$string = str_replace(array("\r\n", '\r\n', "\n\r", '\n\r', "\n", '\n', "\r", '\r'), '<br />', $string);
			return $string;
		}
	}