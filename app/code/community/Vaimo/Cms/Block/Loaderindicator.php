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

class Vaimo_Cms_Block_Loaderindicator extends Vaimo_Cms_Block_Js_Lib
{
    protected $_type = self::TYPE_JQUERY;
    protected $_jsClassName = 'loaderIndicator';
    protected $_constructorParams = array(
        'configuration' => array(
            'lines' => 13,           // The number of lines to draw
            'length' => 20,           // The length of each line
            'width' => 10,            // The line thickness
            'radius' => 30,           // The radius of the inner circle
            'corners' => 1,           // Corner roundness (0..1)
            'rotate' => 0,            // The rotation offset
            'direction' => 1,         // 1: clockwise, -1: counterclockwise
            'color' => '#000',        // #rgb or #rrggbb or array of colors
            'speed' => 1,             // Rounds per second
            'trail' => 60,            // Afterglow percentage
            'shadow' => false,        // Whether to render a shadow
            'hwaccel' => false,       // Whether to use hardware acceleration
            'className' => 'spinner', // The CSS class to assign to the spinner
            'zIndex' => 2e9,          // The z-index (defaults to 2000000000)
            'top' => '42%',           // Top position relative to parent
            'left' => '50%'           // Left position relative to parent
        ),
        'selectors' => array(
            'wrapper' => '#js-vcms-loader-indicator',
            'message' => '#js-vcms-loader-indicator-message'
        )
    );
}
