<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @package     Vaimo_CustomCss
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_CustomCss_Model_System_Config_Source_Theme
{
    public function toOptionArray()
    {
        $options = array();

        $options[] = array('label' => 'Ambiance', 'value' => 'ambiance');
        $options[] = array('label' => 'Chaos', 'value' => 'chaos');
        $options[] = array('label' => 'Chrome', 'value' => 'chrome');
        $options[] = array('label' => 'Clouds', 'value' => 'clouds');
        $options[] = array('label' => 'Clouds Midnight', 'value' => 'clouds_midnight');
        $options[] = array('label' => 'Cobalt', 'value' => 'cobalt');
        $options[] = array('label' => 'Crimson Editor', 'value' => 'crimson_editor');
        $options[] = array('label' => 'Dawn', 'value' => 'dawn');
        $options[] = array('label' => 'Dreamweaver', 'value' => 'dreamweaver');
        $options[] = array('label' => 'Eclipse', 'value' => 'eclipse');
        $options[] = array('label' => 'Github', 'value' => 'github');
        $options[] = array('label' => 'Idle Fingers', 'value' => 'idle_fingers');
        $options[] = array('label' => 'Kr Theme', 'value' => 'kr_theme');
        $options[] = array('label' => 'Kuroir', 'value' => 'kuroir');
        $options[] = array('label' => 'Merbivore', 'value' => 'merbivore');
        $options[] = array('label' => 'Merbivore Soft', 'value' => 'merbivore_soft');
        $options[] = array('label' => 'Mono Industrial', 'value' => 'mono_industrial');
        $options[] = array('label' => 'Monokai', 'value' => 'monokai');
        $options[] = array('label' => 'Pastel On Dark', 'value' => 'pastel_on_dark');
        $options[] = array('label' => 'Solarized Dark', 'value' => 'solarized_dark');
        $options[] = array('label' => 'Solarized Light', 'value' => 'solarized_light');
        $options[] = array('label' => 'Terminal', 'value' => 'terminal');
        $options[] = array('label' => 'Textmate', 'value' => 'textmate');
        $options[] = array('label' => 'Tomorrow', 'value' => 'tomorrow');
        $options[] = array('label' => 'Tomorrow Night', 'value' => 'tomorrow_night');
        $options[] = array('label' => 'Tomorrow Night Blue', 'value' => 'tomorrow_night_blue');
        $options[] = array('label' => 'Tomorrow Night Bright', 'value' => 'tomorrow_night_bright');
        $options[] = array('label' => 'Tomorrow Night Eighties', 'value' => 'tomorrow_night_eighties');
        $options[] = array('label' => 'Twilight', 'value' => 'twilight');
        $options[] = array('label' => 'Vibrant Ink', 'value' => 'vibrant_ink');
        $options[] = array('label' => 'Xcode', 'value' => 'xcode');

        return $options;
    }
}