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

class Vaimo_Cms_Model_Router extends Vaimo_Cms_Model_Abstract
{
    const CONTENT_UPDATE_PARAM_PREFIX = '__';
    const CONTENT_UPDATE_ACTION_PARAM = '__vaimo_cms_action';

    const RESPONSE_FUNCTION_SUFFIX = 'Response';
    const RESPONSE_DEFAULT = 'getResponse';
    const RESPONSE_ALWAYS = 'always';

    protected $_models = array();

    protected $_requiredArguments = array('editors');

    public function __construct(array $args = array())
    {
        $required = $this->_extractRequiredArgs($args);

        $this->_models = $required['editors'];

        parent::__construct($args);
    }

    protected function _getContentUpdateAction($arguments)
    {
        if (isset($arguments[self::CONTENT_UPDATE_ACTION_PARAM])) {
            return $arguments[self::CONTENT_UPDATE_ACTION_PARAM];
        }

        return false;
    }

    protected function _isJsonEncodedString($value)
    {
        return is_string($value) && strlen($value) && ($value[0] == '{' || $value[0] == '[' || $value[0] == '"');
    }

    protected function _getCmsEditorArguments($arguments)
    {
        $editorArguments = array();

        unset($arguments[self::CONTENT_UPDATE_ACTION_PARAM]);

        $prefixLength = strlen(self::CONTENT_UPDATE_PARAM_PREFIX);
        foreach ($arguments as $key => $value) {
            if (substr($key, 0, $prefixLength) != self::CONTENT_UPDATE_PARAM_PREFIX) {
                continue;
            }

            if (substr($key, $prefixLength, 1) == '_') {
                continue;
            }

            if ($this->_isJsonEncodedString($value)) {
                try {
                    $decodedValue = Zend_Json_Decoder::decode($value);

                    if ($decodedValue !== null) {
                        $value = $decodedValue;
                    }
                } catch (Exception $e) {}
            }

            $editorArguments[substr($key, $prefixLength)] = $value;
        }

        return $editorArguments;
    }

    public function init($currentControllerActionName)
    {
        $storeId = $this->getApp()->getStore()->getId();

        foreach ($this->_models as $model) {
            $model->setCurrentControllerActionName($currentControllerActionName);
            $model->setStoreId($storeId);
        }

        return $this;
    }

    public function process($arguments)
    {
        $action = $this->_getContentUpdateAction($arguments);

        if (!$action) {
            return false;
        }

        $editorArguments = $this->_getCmsEditorArguments($arguments);

        $processor = $this->getFactory()->getSingleton('vaimo_cms/editor_model_processor');

        foreach ($this->_models as $model) {
            if (!$model->validateArguments($editorArguments)) {
                continue;
            }

            $map = $model->getActionMap();

            if (!isset($map[$action])) {
                continue;
            }

            $method = $map[$action];

            if (!method_exists($model, $method)) {
                continue;
            }

            $response = $processor->execute($model, $method, $editorArguments);
            $lastArguments = $processor->getUpdatedArguments();

            if ($lastArguments || is_array($lastArguments)) {
                $editorArguments = $lastArguments;
            }

            if (!$response) {
                continue;
            }

            return $response;
        }

        return true;
    }

    public function getResponse($arguments)
    {
        $action = $this->_getContentUpdateAction($arguments);

        if (!$action) {
            return false;
        }

        $editorArguments = $this->_getCmsEditorArguments($arguments);

        $response = false;
        $extra = array();

        foreach ($this->_models as $model) {
            if (!$model->validateArguments($editorArguments)) {
                continue;
            }

            if ($response === false || $response === null) {
                $map = $model->getActionMap();

                if (isset($map[$action])) {
                    $responseFunction = $map[$action] . self::RESPONSE_FUNCTION_SUFFIX;

                    $callable = false;

                    if (method_exists($model, $responseFunction)) {
                        $callable = $responseFunction;
                    } else if (method_exists($model, self::RESPONSE_DEFAULT)) {
                        $callable = self::RESPONSE_DEFAULT;
                    } else if (method_exists($model, $map[$action])) {
                        $callable = $map[$action];
                    }

                    if ($callable) {
                        $response = $model->$callable($editorArguments);
                    }
                }
            }

            if (method_exists($model, self::RESPONSE_ALWAYS)) {
                $callable = self::RESPONSE_ALWAYS;

                $extra = array_merge($model->$callable($editorArguments), $extra);
            }
        }

        if (is_array($response)) {
            $response = array_merge($extra, $response);
        } else if (is_string($response)) {
            $response = array(
                'message' => $response
            );
        }

        return $response;
    }
}