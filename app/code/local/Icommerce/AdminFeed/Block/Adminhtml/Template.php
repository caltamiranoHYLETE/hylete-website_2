<?php

/**
 * Copyright (c) 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_AdminFeed
 * @copyright   Copyright (c) 2009-2011 Icommerce Nordic AB
 */
class Icommerce_AdminFeed_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Template
{

    public function getLatestModules($i = null)
    {
        /* Read all the top modules from the file and store them in an array */
        $modules = array();
        $file = "/var/icommerce/adminfeed" . "/topModules.txt";
        if (file_exists($file) === false) {
            return $modules;
        }
        $fp = fopen($file, 'r');
        fgets($fp);

        $mageLocale = Mage::app()->getLocale()->getLocale();

        while (($line = fgets($fp)) !== FALSE && sizeof($modules) < 10) {
            $line = trim($line);
            $data = explode(";", $line);
            $prio = $data[0];
            $module = $data[1];
            $sv_SE_moduleDescription = $data[2];
            $en_US_moduleDescription = $data[3];

            array_push($modules, str_replace("Icommerce_", "", $module));
            /* Return short description of the module */
            if ("Icommerce_" . $i == $module) {
                if ($mageLocale == "sv_SE")
                    return $sv_SE_moduleDescription;
                else
                    return $en_US_moduleDescription;
            }
        }
        return $modules;
    }

    /**
     * Get our top modules
     */
    public function getTopModules($i = null)
    {
        /* Read all the modules in this category */
        $directory = Mage::getBaseDir('etc') . "/modules";
        $installedModules = array();
        $dirhandler = opendir($directory);
        while ($installedModule = readdir($dirhandler)) {
            if (strstr($installedModule, "Icommerce"))
                array_push($installedModules, str_replace(".xml", "", $installedModule));
        }
        closedir($dirhandler);


        /* Read all the top modules from the file and store them in an array */
        $modules = array();
        $file = "/var/icommerce/adminfeed" . "/topModules.txt";
        $fp = fopen($file, 'r');
        fgets($fp);

        $j = 0;
        while (($line = fgets($fp)) !== FALSE && sizeof($modules) < 10) {
            $line = trim($line);
            $data = explode(";", $line);
            $prio = $data[0];
            $module = $data[1];

            if (!in_array($module, $installedModules)) {
                array_push($modules, str_replace("Icommerce_", "", $module));
                /* Return short description of the module */
                if ($i - 1 == $j)
                    return $data[2];
                else
                    $j++;
            }
        }
        return $modules;
    }

    /**
     * Get our latest news in admin next to our top modules.
     */
    public function getAdminNews($i = null)
    {
        /* Read all messages in the file and store them in an array */
        $messages = array();
        $file = "/var/icommerce/adminfeed" . "/adminNews.txt";
        $fp = fopen($file, 'r');
        fgets($fp);

        $mageLocale = Mage::app()->getLocale()->getLocale();

        $i = 0;
        while (($line = fgets($fp)) !== FALSE && count($messages) < 3) {
            $line = trim($line);
            $data = explode(";", $line);
            $prio = $data[0];
            $title = $data[1];
            $message = $data[2];
            $locale = $data[3];

            if ($mageLocale == "sv_SE")        /* Supported language is Swedish */ {
                if ($locale == $mageLocale) {
                    $messages[$i][0] = $data[1];
                    $messages[$i][1] = $data[2];
                }
            } else                            /* If Magento language is not one of the supported above, use English as default */ {
                if ($locale == "en_US") {
                    $messages[$i][0] = $data[1];
                    $messages[$i][1] = $data[2];
                }
            }
            $i++;
        }
        return $messages;
    }

    public function getModuleVersion($module, $i)
    {
        $file = file(Mage::getBaseDir('base') . "/" . $module . ".mod");
        $version = explode("/", $file[0]);
        if ($i == "latest") {
            if ($version[1])
                return $version[1];
            else
                return "n/a";
        } else {
            if ($version[0])
                return $version[0];
            else
                return "n/a";
        }
    }

    public function getInstalledModules()
    {
        $hiddenModules = $this->_getHiddenModules();
        /* Read all the modules in this category */
        $directory = Mage::getBaseDir('etc') . "/modules";
        $installedModules = array();
        $dirhandler = opendir($directory);
        while ($installedModule = readdir($dirhandler)) {
            $installedModule = str_replace(".xml", "", $installedModule);
            if ((!strstr($installedModule, "Mage"))
                && (!strstr($installedModule, "."))
                && (!$this->_isHidden($installedModule, $hiddenModules))
            ) {
                array_push($installedModules, $installedModule);
            }
        }
        closedir($dirhandler);
        return $installedModules;
    }

    protected function _getHiddenModules()
    {
        /* Read all messages in the file and store them in an array */
        $hiddenModules = array();
        $file = "/var/icommerce/adminfeed" . "/hiddenModules.txt";
        $fp = fopen($file, 'r');
        fgets($fp);

        while (($line = fgets($fp)) !== FALSE) {
            $line = trim($line);
            $data = explode(";", $line);
            array_push($hiddenModules, $data[0]);
        }
        return $hiddenModules;
    }

    protected function _isHidden($installedModule, $hiddenModules)
    {
        if (in_array($installedModule, $hiddenModules))
            return true;
        else
            return false;
    }
}