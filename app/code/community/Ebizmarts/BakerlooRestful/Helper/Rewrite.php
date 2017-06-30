<?php

class Ebizmarts_BakerlooRestful_Helper_Rewrite extends Mage_Core_Helper_Abstract {

    public function getConflictList() {

        $bakerlooRewrites = $this->getBakerlooRewrites();
        $rewrites = $this->getAllRewrites();

        $conflicts = array();

        foreach($bakerlooRewrites as $from => $to) {

            $model = Mage::getModel($from);

            if(!($model instanceof $to) and isset($rewrites[$from])) {

                $conflicts[$from] = $rewrites[$from];
                $conflicts[$from][] = $to;

            }
        }

        return $conflicts;
    }

    /**
     * @return array ['fromA' => 'toB']
     */
    public function getBakerlooRewrites() {

        $dir = Mage::getBaseDir('code') . DS . 'community' . DS . 'Ebizmarts';

        $xmls = glob($dir . DS . 'Bakerloo*' . DS . 'etc' . DS . 'config.xml', GLOB_NOCHECK);

        $config = new Varien_Simplexml_Config();

        $rewrites = array();

        foreach($xmls as $xml) {

            $config->loadFile($xml);

            $moduleRewrites = $config->getXpath('global/*/*/rewrite');

            if($moduleRewrites) {

                foreach ($moduleRewrites as $node) {

                    $children = $node->children();
                    if(!empty($children)) {

                        $parent = $node->getParent()->getName();
                        $node = $node->asArray();

                        $children = array_keys($node);

                        $parent .= '/' . $children[0];
                        $rewrites[$parent] = $node[$children[0]];
                    }

                }

            }
        }

        return $rewrites;
    }

    /**
     * @return array ['fromA' => 'toB']
     */
    public function getAllRewrites() {

        $baseDir = Mage::getBaseDir('code') . DS;
        $dirs = array(
            $baseDir . 'community',
            $baseDir . 'local'
        );

        $xmls = array();
        foreach($dirs as $dir) {
            $confs = glob($dir . DS . '*' . DS . '*' . DS . 'etc' . DS . 'config.xml', GLOB_NOCHECK);

            if($confs)
                $xmls = array_merge($xmls, $confs);
        }

        $config = new Varien_Simplexml_Config();

        $rewrites = array();

        foreach($xmls as $xml) {

            if( 1 === preg_match('/Bakerloo/', $xml) )
                continue;

            $config->loadFile($xml);

            $moduleRewrites = $config->getXpath('global/*/*/rewrite');

            if($moduleRewrites) {

                foreach ($moduleRewrites as $node) {

                    $parent = $node->getParent()->getName();
                    $node = $node->asArray();

                    if(!is_array($node))
                        continue;

                    $children = array_keys($node);

                    $parent .= '/' . $children[0];
                    $rewrites[$parent][] = $node[$children[0]];
                }

            }
        }

        return $rewrites;
    }
}