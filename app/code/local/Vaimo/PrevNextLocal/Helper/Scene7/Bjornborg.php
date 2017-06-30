<?php
/**
 * Copyright(c) 2009 - 2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered . The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence . A licence
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
 * @package     Vaimo_PrevNextLocal
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Peter Lembke <peter.lembke@vaimo.com>
 */

/**
 * This is an example how you can support Scene7.
 * Point to your helper in the module admin config
 * Class Vaimo_PrevNextLocal_Helper_Scene7_Bjornborg
 */
class Vaimo_PrevNextLocal_Helper_Scene7_Bjornborg extends Mage_Core_Helper_Abstract
{
    public function getBaseImageUrl() {
        return 'http://bjornborg.scene7.com/is/image/bjornborg/';
    }

    public function getProductImageUrl($product) {
        // return 'http://bjornborg.scene7.com/is/image/bjornborg/151215-109031_90011_1?$BB450$&fmt=jpeg';
        return '151215-109031_90011_1?$BB450$&fmt=jpeg';
    }

}
