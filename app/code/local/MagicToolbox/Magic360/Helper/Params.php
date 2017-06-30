<?php

class MagicToolbox_Magic360_Helper_Params extends Mage_Core_Helper_Abstract {

    public function __construct() {

    }

    public function checkForOldModules() {
        static $oldModulesInstalled = null;
        if($oldModulesInstalled === null) {
            $oldModulesInstalled = array();
            $modules = array(
                'magicthumb' => 'Magic Thumb',
                'magiczoom' => 'Magic Zoom',
                'magiczoomplus' => 'Magic Zoom Plus',
                'magicscroll' => 'Magic Scroll',
                'magicslideshow' => 'Magic Slideshow',
            );
            $inModules = "'".implode("_setup', '", array_keys($modules))."_setup'";
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $table = $resource->getTableName('core/resource');
            $result = $connection->query("SELECT * FROM `{$table}` WHERE `code` IN ({$inModules})");
            if($result) {
                while($module = $result->fetch(PDO::FETCH_ASSOC)) {
                    if(version_compare($module['version'], '4.12.0', '<')) {
                        $key = str_replace('_setup', '', $module['code']);
                        if($this->isModuleEnabled('MagicToolbox_'.str_replace(' ', '', $modules[$key]))) {
                            $oldModulesInstalled[] = array('name' => $modules[$key], 'version' => $module['version']);
                        }
                    }
                }
            }
        }
        return $oldModulesInstalled;
    }

    public function getFixedDefaultValues() {
        $defaultValues = self::getDefaultValues();
        foreach($defaultValues as $platform => $platformData) {
            foreach($platformData as $profile => $profileData) {
                foreach($profileData as $param => $value) {
                    if($param == 'enable-effect' || $param == 'include-headers-on-all-pages') {
                        $defaultValues[$platform][$profile][$param] = 'No';
                    }
                }
            }
        }
        return $defaultValues;
    }

    public function getProfiles() {
        return array(
            'default' => 'Defaults',
			'product' => 'Product page'
        );
    }

    public function getDefaultValues() {
        return array(
            'desktop' => array(
				'product' => array(
					'enable-effect' => 'Yes'
				)
			),
			'mobile' => array(
			)
        );
    }

    public function getParamsMap($block) {
        $blocks = array(
            'default' => array(
				'General' => array(
					'include-headers-on-all-pages'
				),
				'Magic 360' => array(
					'magnify',
					'magnifier-width',
					'magnifier-shape',
					'fullscreen',
					'spin',
					'autospin-direction',
					'sensitivityX',
					'sensitivityY',
					'mousewheel-step',
					'autospin-speed',
					'smoothing',
					'autospin',
					'autospin-start',
					'autospin-stop',
					'initialize-on',
					'start-column',
					'start-row',
					'loop-column',
					'loop-row',
					'reverse-column',
					'reverse-row',
					'column-increment',
					'row-increment'
				),
				'Positioning and Geometry' => array(
					'thumb-max-width',
					'thumb-max-height',
					'square-images'
				),
				'Miscellaneous' => array(
					'icon',
					'show-message',
					'message',
					'loading-text',
					'fullscreen-loading-text',
					'hint',
					'hint-text',
					'mobile-hint-text'
				)
			),
			'product' => array(
				'General' => array(
					'enable-effect'
				),
				'Magic 360' => array(
					'magnify',
					'magnifier-width',
					'magnifier-shape',
					'fullscreen',
					'spin',
					'autospin-direction',
					'sensitivityX',
					'sensitivityY',
					'mousewheel-step',
					'autospin-speed',
					'smoothing',
					'autospin',
					'autospin-start',
					'autospin-stop',
					'initialize-on',
					'start-column',
					'start-row',
					'loop-column',
					'loop-row',
					'reverse-column',
					'reverse-row',
					'column-increment',
					'row-increment'
				),
				'Positioning and Geometry' => array(
					'thumb-max-width',
					'thumb-max-height',
					'square-images'
				),
				'Miscellaneous' => array(
					'icon',
					'show-message',
					'message',
					'hint'
				)
			)
        );
        return $blocks[$block];
    }
}
