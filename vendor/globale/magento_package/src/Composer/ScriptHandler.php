<?php

namespace Globale\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ScriptHandler is used by composer to install Globale modules
 *
 * @package Globale\Composer
 */
class ScriptHandler
{
    const GLOBALE_BASE           = 'Globale_Base';
    const GLOBALE_BROWSING       = 'Globale_Browsing';
    const GLOBALE_BROWSING_LITE  = 'Globale_BrowsingLite';
    const GLOBALE_ORDER          = 'Globale_Order';
    const GLOBALE_FIXEDPRICES    = 'Globale_FixedPrices';
    const GLOBALE_EXTERNAMEERP   = 'Globale_ExternalERP';
    const GLOBALE_DATAPROVIDER   = 'Globale_DataProvider';
    const GLOBALE_EFPC           = 'Globale_EFPC';

    /**
     * Composer command entry point
     * @param Event $Event
     */
    public static function configureModules(Event $Event)
    {
        $Event->getIO()->write('Globale module configurator - ok');
        $Extra = $Event->getComposer()->getPackage()->getExtra();
        $RootDir = getcwd() . '/' . $Extra['magento-root-dir'];

        if(isset($Extra['magento-deploystrategy'])){
            $Symlink = $Extra['magento-deploystrategy'] == "copy" ? false : true;
        }else{
            $Symlink = false;
        }


        //clean directories
        static::clearConfigs($RootDir);
        static::clearModuleFolders($RootDir);

        $ModulesEnabled = array();


        //check modules dependencies and include them to enabled modules list
        if (isset($Extra['globale-enabled-modules'])) {
            $ModulesEnabled = $Extra['globale-enabled-modules'];
            $Dependencies = static::getModuleDependencies();
            foreach ($ModulesEnabled as $Module) {
                $ModuleDependencies = $Dependencies[$Module];
                foreach ($ModuleDependencies as $moduleDependency) {
                    if (!in_array($moduleDependency, $ModulesEnabled)) {
                        $ModulesEnabled[] = $moduleDependency;
                    }
                }
            }
        }


        $Event->getIO()->write("Configure Globale modules");

        //enable modules
        foreach (static::getModules() as $Title => $Module) {
            if (sizeof($ModulesEnabled) > 0) {
                $Enabled = false;
                if (in_array($Module, $ModulesEnabled)) {
                    $Enabled = true;
                }
            } else {
                $Enabled = $Event->getIO()->askConfirmation('Enable module ' . $Title . ' [Y/n] ', true);
            }
            $Event->getIO()->write('Initializing module ' . $Module);
            if ($Enabled) {
                static::initModule($Module, $RootDir, $Symlink);
            }
        }

    }

    /**
     * Clear config folder
     * @param $Root
     */
    protected static function clearConfigs($Root)
    {
        $ConfigRoot = $Root . '/app/etc/modules/';
        $Fs = new Filesystem();
        foreach (static::getModules() as $Title => $Module) {
            $ModuleConfig = $ConfigRoot . $Module . '.xml';
            $Fs->remove($ModuleConfig);
        }
    }

    /**
     * Get list of supported modules with their descriptiond
     * @return array
     */
    protected static function getModules()
    {
        return array(
            "Base"                   => static::GLOBALE_BASE,
            "Browsing"               => static::GLOBALE_BROWSING,
            "Browsing Lite"          => static::GLOBALE_BROWSING_LITE,
            "Order"                  => static::GLOBALE_ORDER,
            "Fixed Prices"           => static::GLOBALE_FIXEDPRICES,
            "External ERP"           => static::GLOBALE_EXTERNAMEERP,
            "Data Provider"          => static::GLOBALE_DATAPROVIDER,
            "Enterprise FPC support" => static::GLOBALE_EFPC
        );
    }

    /**
     * Clear module folders
     * @param $Root
     * @throws \Exception
     */
    protected static function clearModuleFolders($Root)
    {
        $ModulesRoot = $Root . '/app/code/community/Globale';
        $Fs = new Filesystem();

        if (is_dir($ModulesRoot) && !is_writeable($ModulesRoot)) {
            throw new \Exception('Directory ' . $ModulesRoot . ' should be writeable');
        }

        if (is_dir($ModulesRoot)) {
            $Fs->remove($ModulesRoot);
            mkdir($ModulesRoot);
        }
    }

    /**
     * Get module dependency tree
     * @return array
     */
    protected static function getModuleDependencies()
    {
        return array(
            static::GLOBALE_BASE            => static::getDependencies(static::GLOBALE_BASE),
            static::GLOBALE_BROWSING        => static::getDependencies(static::GLOBALE_BROWSING),
            static::GLOBALE_BROWSING_LITE   => static::getDependencies(static::GLOBALE_BROWSING_LITE),
            static::GLOBALE_DATAPROVIDER    => static::getDependencies(static::GLOBALE_DATAPROVIDER),
            static::GLOBALE_EFPC            => static::getDependencies(static::GLOBALE_EFPC),
            static::GLOBALE_EXTERNAMEERP    => static::getDependencies(static::GLOBALE_EXTERNAMEERP),
            static::GLOBALE_FIXEDPRICES     => static::getDependencies(static::GLOBALE_FIXEDPRICES),
            static::GLOBALE_ORDER           => static::getDependencies(static::GLOBALE_ORDER)
        );
    }

    /**
     * Get module dependency from module configuration file
     * @param $Module
     * @return array
     */
    protected static function getDependencies($Module)
    {
        $doc = new \DOMDocument();
        $doc->load(static::getModuleConfigSourcePath($Module));
        $depsNodes = $doc->getElementsByTagName('depends')->item(0)->childNodes;
        $result = array();
        foreach ($depsNodes as $node) {
            $value = $node->nodeName;
            if (static::strStartsWith($value, "Globale_")) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Return real path to module config file
     * @param $Module
     * @return string
     */
    protected static function getModuleConfigSourcePath($Module)
    {
        return realpath(__DIR__ . '/../../' . 'app/etc/modules/') . '/' . $Module . '.xml';
    }

    /**
     * Check is string starts with given $search
     * @param $string
     * @param $search
     * @param bool $caseSensitive
     * @return bool
     */
    protected static function strStartsWith($string, $search, $caseSensitive = false)
    {
        if (!is_string($string)) {
            return false;
        }

        if (!is_array($search)) {
            $search = [$search];
        }

        foreach ($search as $item) {
            $match = substr($string, 0, strlen($item));

            if ($caseSensitive) {
                if ($match == $item) {
                    return true;
                }
            } else {
                if (strcasecmp($match, $item) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Initialize module by its name. Copy config file and source files in to magento installation
     * @param $Module
     * @param $Root
     * @param bool $Symlink
     */
    protected static function initModule($Module, $Root, $Symlink = false)
    {
        $Fs = new Filesystem();
        $ConfigSource = static::getModuleConfigSourcePath($Module);
        $ConfigTarget = $Root . '/app/etc/modules/' . $Module . '.xml';

        $Fs->copy($ConfigSource, $ConfigTarget);


        $Base = '/app/code/community/Globale/' . str_replace('Globale_', '', $Module);
        $Source = realpath(__DIR__ . '/../../' . $Base);
        $Target = $Root . $Base;
        echo 'target ' . $ConfigTarget . PHP_EOL . 'source ' . $ConfigSource . PHP_EOL;

        if (!$Symlink) {
            $Fs->mirror($Source, $Target);
        } else {
            $Fs->symlink($Source, $Target);
        }

    }

    /**
     * Remove directory recursively
     * @param $Dir
     */
    protected static function rrmdir($Dir)
    {
        if (is_dir($Dir)) {
            $Objects = scandir($Dir);
            foreach ($Objects as $Object) {
                if ($Object != "." && $Object != "..") {
                    if (is_dir($Dir . "/" . $Object)) {
                        static::rrmdir($Dir . "/" . $Object);
                    } else {
                        unlink($Dir . "/" . $Object);
                    }
                }
            }
            if (is_link($Dir)) {
                unlink($Dir);
            } else {
                rmdir($Dir);
            }
        }
    }

}