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

class Vaimo_Cms_Helper_Runtime extends Vaimo_Cms_Helper_Abstract
{
    const REGISTRY_PREFIX_SINGLETON = '_singleton/';

    public function singletonExists($type)
    {
        return (bool)Mage::registry(self::REGISTRY_PREFIX_SINGLETON . $type);
    }

    public function unsetSingleton($type)
    {
        Mage::unregister(self::REGISTRY_PREFIX_SINGLETON . $type);
    }

    public function getNewSingleton($type)
    {
        Mage::unregister(self::REGISTRY_PREFIX_SINGLETON . $type);

        return $this->getFactory()->getSingleton($type);
    }

    public function functionExists($name)
    {
        return function_exists($name);
    }

    public function executeWithTmpReplacedModelData(
        Varien_Object $source, Varien_Object $target, array $keys, Closure $callable
    ) {
        $_keys = array_flip($keys);

        $targetData = array_intersect_key($target->getData(), $_keys);
        $sourceData = array_intersect_key($source->getData(), $_keys);

        $target->addData($sourceData);

        $result = $callable();

        $target->addData($targetData);

        return $result;
    }
}