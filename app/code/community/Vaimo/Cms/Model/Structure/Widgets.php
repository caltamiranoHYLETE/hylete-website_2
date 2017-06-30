<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
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
 * @category    Vaimo
 * @package     Vaimo_Cms
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Vaimo_Cms_Model_Structure_Widgets extends Vaimo_Cms_Model_Editor_Abstract
{
    public function generateParameters($type, $handle, $blockReference)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Widget_ManagerPool $managerPool */
        $managerPool = $factory->getSingleton('vaimo_cms/widget_managerPool');
        $managerConfig = $managerPool->getConfigForType($type);

        if (!$managerConfig) {
            return array();
        }

        /** @var Vaimo_Cms_Model_Widget_ManagerInterface $widgetManager */
        $widgetManager = $factory->getSingleton($managerConfig['type']);

        $parameters = $widgetManager->generateParams(
            $handle,
            $blockReference
        );

        if (isset($managerConfig['defaults']) && is_array($managerConfig['defaults'])) {
            $parameters = array_replace($parameters, $managerConfig['defaults']);
        }

        return $parameters;
    }

    public function createParameters($type, $parameters)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Widget_ManagerPool $managerPool */
        $managerPool = $factory->getSingleton('vaimo_cms/widget_managerPool');
        $managerConfig = $managerPool->getConfigForType($type);

        if (!$managerConfig) {
            return $parameters;
        }

        /** @var Vaimo_Cms_Model_Widget_ManagerInterface $widgetManager */
        $widgetManager = $factory->getSingleton($managerConfig['type']);

        return array_replace(
            $parameters,
            $widgetManager->createParams($parameters)
        );
    }

    public function publishParameters($type, $parameters)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Widget_ManagerPool $managerPool */
        $managerPool = $factory->getSingleton('vaimo_cms/widget_managerPool');
        $managerConfig = $managerPool->getConfigForType($type);

        if (!$managerConfig) {
            return $parameters;
        }

        /** @var Vaimo_Cms_Model_Widget_ManagerInterface $widgetManager */
        $widgetManager = $factory->getSingleton($managerConfig['type']);

        return array_replace(
            $parameters,
            $widgetManager->publishParams($parameters)
        );
    }

    public function cloneParameters($type, $parameters)
    {
        $factory = $this->getFactory();

        /** @var Vaimo_Cms_Model_Widget_ManagerPool $managerPool */
        $managerPool = $factory->getSingleton('vaimo_cms/widget_managerPool');
        $managerConfig = $managerPool->getConfigForType($type);

        if (!$managerConfig) {
            return $parameters;
        }

        /** @var Vaimo_Cms_Model_Widget_ManagerInterface $widgetManager */
        $widgetManager = $factory->getSingleton($managerConfig['type']);

        return array_replace(
            $parameters,
            $widgetManager->cloneParams($parameters)
        );
    }
}
