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
 * @package     Icommerce_SlideshowManager
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 *
 * Template text preview field renderer
 *
 */
class Icommerce_SlideshowManager_Model_Text implements Varien_Data_Form_Element_Renderer_Interface
{
    const HTML_IFRAME_PREVIEW_TEMPLATE = '<iframe src="%s" id="%s" frameborder="0" class="template-preview"> </iframe>';
    const NEWLINE_CHAR = "\n";

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr><td class="label">' . self::NEWLINE_CHAR;
        if ($label = $element->getLabel()) {
            $html .= '<label for="' . $element->getHtmlId() . '">' . $label . '</label>' . "\n";
        }
        $html .= '</td><td class="value">';
        $html .= sprintf(self::HTML_IFRAME_PREVIEW_TEMPLATE, $element->getValue(), $element->getHtmlId());
        $html .= '</td><td></td></tr>' . self::NEWLINE_CHAR;

        return $html;
    }

    public function getItem($itemId)
    {
        $itemId = (int )$itemId;
        $row = Icommerce_Db::getRow("SELECT * FROM icommerce_slideshow_item WHERE id = $itemId");

        return $row;
    }

}
