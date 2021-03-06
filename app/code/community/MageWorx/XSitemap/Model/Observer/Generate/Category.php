<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 *
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_XSitemap_Model_Observer_Generate_Category
{
    public function addGenerator($observer)
    {
        $container  = $observer->getEvent()->getContainer();
        $generators = $container->getGenerators();

        $helper   = Mage::helper('mageworx_xsitemap');
        $titleKey = MageWorx_XSitemap_Model_GeneratorFactory::TITLE_KEY;
        $modelKey = MageWorx_XSitemap_Model_GeneratorFactory::MODEL_KEY;

        $generatorName = 'category_by_event';

        $generators[$generatorName] = array();
        $generators[$generatorName][$titleKey] = $helper->__('Generated Third-party URLs (category)');
        $generators[$generatorName][$modelKey] = 'mageworx_xsitemap/generator_categoryByEvent';

        $container->setGenerators($generators);

        return $this;
    }
}