<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
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
 * @package     Vaimo_Module
 * @file        Slideshow.php
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Vitali Rassolov <vitali.rassolov@vaimo.com>
 */

class Vaimo_Hylete_Helper_Slideshow extends Mage_Core_Helper_Abstract
{
    const TEXT_PLACEMENT_LEFT = 1;
    const TEXT_PLACEMENT_CENTER = 2;
    const TEXT_PLACEMENT_RIGHT = 3;

    const ALIGN_TEXT_LEFT = 1;
    const ALIGN_TEXT_CENTER = 2;
    const ALIGN_TEXT_RIGHT = 3;

    const NON_INVERT = 0;
    const INVERT = 1;

    public function getTextPlacementOptions()
    {
        return array(
            self::TEXT_PLACEMENT_LEFT => $this->__('Left'),
            self::TEXT_PLACEMENT_CENTER => $this->__('Center'),
            self::TEXT_PLACEMENT_RIGHT => $this->__('Right'),
        );
    }

    public function getAlignTextOptions()
    {
        return array(
            self::ALIGN_TEXT_LEFT => $this->__('Left'),
            self::ALIGN_TEXT_CENTER => $this->__('Center'),
            self::ALIGN_TEXT_RIGHT => $this->__('Right'),
        );
    }

    public function getInvertOptions()
    {
        return array(
            self::NON_INVERT => $this->__('No'),
            self::INVERT => $this->__('Yes'),
        );
    }
}
