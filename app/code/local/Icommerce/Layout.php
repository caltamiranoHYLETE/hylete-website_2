<?php

class Icommerce_Layout {

    static $design;
    static function getTemplatePath( $template ){
        if( !self::$design ){
            self::$design = Mage::getDesign();
        }
        $params = array(
            "_relative" => TRUE );
        $file = self::$design->getTemplateFilename($template, $params);
        return $file ? "app/design/$file" : null;
    }

    static function templateInclude( $template/*, $tmplt_args=null*/ ){
        $path = self::getTemplatePath( $template );
        if( $path ){
            /*if( $tmplt_args ){
                if( !is_array($tmplt_args) ){
                    Mage::throwException( "templateInclude: locals must be an array" );
                }
                extract( $tmplt_args );
            }*/
            include( $path );
        } else {
            Mage::throwException( "templateInclude - Could locate template: $template" );
        }
    }


    static $layout;
    static function getBlockHtml( $type, $template=null, $data=array() ){
        if( !self::$layout ){
            self::$layout = Mage::getSingleton('core/layout');
        }

        if( !$type instanceof Mage_Core_Block_Abstract ){
            $block = self::$layout->createBlock( $type );
            if( !$block ){
                Mage::throwException( "getBlockHtml - Could not instantiate block: $type :: $template" );
            }
        } else {
            // Special case, got ready made block here
            $block = $type;
        }

        $template_prv = $block->getTemplate();
        $lifetime_prv = null;
        if( $template && $template!=$template_prv ){
            $block->setTemplate( $template );
            // This is to disable caching if a block is reused with another "inner" template
            $lifetime_prv = $block->getData("cache_lifetime");
            $block->setData( "cache_lifetime", null );
        }

        if( $data ){
            if( !is_array($data) ){
                Mage::throwException( "getBlockHtml: data must be an array" );
            }
            foreach( $data as $k => $v ){
                $block->setData($k,$v);
            }
        }
        $html = $block->toHtml();

        // Restore the template
        if( $template && $template!=$template_prv ){
            $block->setTemplate( $template_prv );
            $block->setData( "cache_lifetime", $lifetime_prv );
        }

        return $html;
    }

    static protected function getBlockNested( Mage_Core_Block_Abstract $block, $type ){
        // Scan this level + recursively (depth first)
        foreach( $block->getChild() as $child ){
            if( $child instanceof $type ) return $child;
            $r = self::getBlockNested( $child, $type );
            if( $r ) return $r;
        }
    }

    static function findBlockInLayout( $type, $name="" ){
        if( !$type ) return null;

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton( 'core/layout' );

        // Search by name first
        if( $block = $layout->getBlock($name) ){
            if( $block instanceof $type ) return $block;
        }

        // Search by type
        if( !($root = $layout->getBlock("root")) ) return null;
        if( $root instanceof $type ) return $root;

        return self::getBlockNested( $root, $type );
    }

    /** Find all blocks with a certain type in xml.
     * @param $type Class name
     * @return array
     */
    static function findAllBlocksInLayoutWithType($type){
        $blocks = array();
        $layout = Mage::getSingleton( 'core/layout' );
        $allBlocks = $layout->getAllBlocks();

        foreach($allBlocks as $key => $block){
            if($block instanceof $type){
                $blocks[$key] = $block;
            }
        }

        return $blocks;
    }

    static function getLayout(){
        /** @var $st_layout Mage_Core_Model_Layout */
        static $st_layout;
        if( !$st_layout ) $st_layout = Mage::getSingleton( "core/layout" );
        return $st_layout;
    }

    static function interpretAction( $action ){
        if( !($method = (string)$action['method']) ) return null;
        if( $ifconfig = (string)$action['ifconfig'] ){
            if( !Mage::getStoreConfig($ifconfig) ) return null;
        }

        /* This is loosely based on similar code in Mage_Core_Model_Layout::_generateAction */
        $actionData = array( "method" => $method );
        $args = (array)$action->children();
        unset($args['@attributes']);
        foreach( $args as $key => $arg ){
            if( is_string($arg) ){
                $args[$key] = $arg;
            } elseif (($arg instanceof Mage_Core_Model_Layout_Element)) {
                if (isset($arg['helper'])) {
                    $helperName = explode('/', (string)$arg['helper']);
                    $helperMethod = array_pop($helperName);
                    $helperName = implode('/', $helperName);
                    $arg = $arg->asArray();
                    unset($arg['@']);
                    $args[$key] = call_user_func_array(array(Mage::helper($helperName), $helperMethod), $arg);
                }
            } else {
                $arr = array();
                foreach($arg as $subkey => $value) {
                    $arr[(string)$subkey] = $value->asArray();
                }
                if (!empty($arr)) {
                    $args[$key] = $arr;
                }
            }
        }

        if (isset($action['json'])) {
            $json = explode(' ', (string)$action['json']);
            foreach ($json as $arg) {
                $args[$arg] = Mage::helper('core')->jsonDecode($args[$arg]);
            }
        }

        $actionData["args"] = $args;

        return $actionData;
    }

    static function getBlockLayoutData( $type, $name="", $layoutNode=null ){
        $block = $type instanceof Mage_Core_Block_Abstract ? $type : Icommerce_Layout::findBlockInLayout( $type, $name );
        if( $block ){
            if( $block->getNameInLayout() ){
                $name = $block->getNameInLayout();
            }
            $template = null;
            if( !$layoutNode ){
                if( $layoutNode = self::getLayout()->getXpath( "//block[@name='$name']" ) ){
                    $layoutNode = $layoutNode[0];
                }
            }
            if( $layoutNode ){
                /** @var $layoutNode Mage_Core_Model_Config_Element */
                $template = $layoutNode->getAttribute("template");
            }

            // Data about this block
            $layout = array(
                "type" => get_class($block),
                "name" => $name,
                "template" => $template,
                "data" => array(),
                "actions" => array()
            );

            /*foreach( $block->getData() as $k => $v ){
                if( !is_object($v) ) $layout["data"][$k] = $v;
            }*/

            // Parse out the actions
            if( $actions = self::getLayout()->getXpath( "//*[@name='$name']/action" ) ){
                foreach( $actions as $action ){
                    if( $action = self::interpretAction($action) ){
                        $layout["actions"][] = $action;
                    }
                }
            }

            // Loop over child blocks
            $children = array();
            foreach( $block->getChild() as $child ){
                $name = $child->getNameInLayout();
                // Does this block exist in layout ?
                $layoutNode = self::getLayout()->getXpath( "//block[@name='$name']" );
                if( $layoutNode ){
                    $children[] = self::getBlockLayoutData($child,$name,$layoutNode[0]);
                }
            }
            $layout["children"] = $children;
            return $layout;
        }
    }

    static function createBlocksFromLayoutData( array $layout ){
        $block = self::getLayout()->createBlock( $layout["type"], $layout["name"], $layout["data"] );
        if( isset($layout["template"]) ){
            $block->setTemplate( $layout["template"] );
        }

        // Run the actions
        if( isset($layout["actions"]) ){
            foreach( $layout["actions"] as $actionData ){
                $method = $actionData["method"];
                $args = $actionData["args"];
                call_user_func_array(array($block, $method), $args);
            }
        }

        // Do the children
        foreach( $layout["children"] as $childLayout ){
            if( $childBlock = self::createBlocksFromLayoutData( $childLayout ) ){
                $block->append( $childBlock );
            }
        }
        return $block;
    }

    /**
     * The function will apply custom layout design changes from a category, in a backward compatible way
     * @param string $version
     */
    static function applyDesignUpdatesCompatibility( $category ){
        // Try if newer Mage::getSingleton('catalog/design')->getDesignSettings() is available.
        // if not, fall back to previous variant
        $design = Mage::getSingleton('catalog/design');
        $update = self::getLayout()->getUpdate();
        try {
            // New style
            $settings = $design->getDesignSettings($category);
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }
        } catch( Exception $e ){
            // Old style: Do previous way (1.4 and maybe some more)
            $update->addUpdate($category->getCustomLayoutUpdate());
        }
    }

}

