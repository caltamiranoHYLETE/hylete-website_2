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
 * @package     Vaimo_Carbon
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @comment     Complementary module to theme_carbon
 */

class Vaimo_Carbon_Helper_Text extends Mage_Core_Helper_Abstract
{
    public function wrapMarkedValuesInText($text, array $wrapperData)
    {
        foreach($wrapperData as $token => $wrapper) {
            $components = $this->splitTextToComponents($text, $token);

            foreach ($components['variables'] as &$variable) {
                $variable = sprintf($wrapper, $variable);
            }

            $text = $this->mergeComponentsToText($components);
        }

        return $text;
    }

    public function replaceVariables($text, $variables)
    {
        $keys = array_map(function($item) { return '{' . $item . '}'; }, array_keys($variables));

        return str_replace($keys, $variables, $text);
    }


    public function mergeComponentsToText($components)
    {
        return vsprintf($components['template'], $components['variables']);
    }

    public function splitTextToComponents($originalText, $wrapperCharacter = '*')
    {
        $data = array(
            'template' => $this->getTextTemplate($originalText, $wrapperCharacter),
            'variables' => $this->getTextTokens($originalText, $wrapperCharacter)
        );

        return $data;
    }

    public function getTextTokens($text, $wrapperCharacter = '*')
    {
        $tokens = $this->_loopTokenText($text, $wrapperCharacter, function($word, $isToken) {
            return $isToken ? $word : false;
        });

        return $tokens;
    }

    public function getTextTemplate($text, $wrapperCharacter = '*')
    {
        $textWithPlaceholders = $this->_loopTokenText($text, $wrapperCharacter, function($word, $isToken) {
            return $isToken ? '%s' : $word;
        });

        return implode('', $textWithPlaceholders);
    }

    protected function _loopTokenText($text, $wrapperCharacter, Closure $analyzer)
    {
        $originalTextWords = explode($wrapperCharacter, $text);
        $offset = $this->_startsWithWrappedWord($text, $wrapperCharacter) ? 1 : 2;
        $tokenText = array();

        foreach($originalTextWords as $i => $word) {
            $analyzedWord = $analyzer($word, ($i + $offset) % 2);
            if ($analyzedWord !== false) {
                $tokenText[] = $analyzedWord;
            }
        }

        return $tokenText;
    }

    protected function _startsWithWrappedWord($text, $wrapperCharacter)
    {
        $text = trim(strlen($text));
        return strlen($text) && substr(trim($text), 0, 1) == $wrapperCharacter;
    }
}