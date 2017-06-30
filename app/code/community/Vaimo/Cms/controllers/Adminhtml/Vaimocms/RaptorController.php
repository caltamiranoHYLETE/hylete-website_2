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

class Vaimo_Cms_Adminhtml_Vaimocms_RaptorController extends Vaimo_Cms_Controller_Adminhtml_Editor_Action
{
    /**
     * @var Vaimo_Cms_Model_Editor_Raptor
     */
    protected $_raptor;

    /**
     * @var Vaimo_Cms_Model_FileManager_Creator
     */
    protected $_fileManagerCreator;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response,
                                array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        $this->_raptor = isset($invokeArgs['raptor']) ?
            $invokeArgs['raptor'] : $this->getFactory()->getModel('vaimo_cms/editor_raptor');

        $this->_fileManagerCreator = isset($invokeArgs['fileManagerCreator']) ?
            $invokeArgs['fileManagerCreator'] : $this->getFactory()->getModel('vaimo_cms/fileManager_creator');
    }

    /**
     * Main action called by the file manager
     */
    public function fileManagerAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'application/json;');

        $params = $request->getParams();

        if (empty($params['action'])) {
            $this->_setHttpResponseError($response, 'Action parameter must be set', 400);
            return;
        }

        $action = $params['action'];

        $path = '';
        if (isset($params['path'])) {
            $path = $params['path'];
        }

        $searchString = '';
        if (isset($params['search'])) {
            $searchString = $params['search'];
        }

        $result = null;

        switch($action) {
            case 'list':
                $result = $this->_raptor->getFiles($path, $params['start'], $params['limit'], $searchString);
                break;
            case 'upload':
                $result = $this->_raptor->uploadFile('file');
                break;
            case 'save':
                $image = $this->_fileManagerCreator->createFileFromBase64($path, $params['image']);
                $result = $this->_raptor->saveImage($image);
                break;
            case 'view':
                $this->_renderImage($path);
                break;
            case 'delete':
                $result = $this->_raptor->deleteFile($path);
                break;
            default:
                $this->_setHttpResponseError($response, 'Action method not allowed', 400);
                break;
        }

        if ($result != null) {
            $response->setBody($this->getFactory()->getHelper('core')->jsonEncode($result));
        }
    }

    /**
     * Action called by the file manager to show image thumbnails in the image list
     */
    public function thumbnailAction()
    {
        $this->_renderImage($this->getRequest()->getParam('file'), true);
    }

    /**
     * Action called after editing an image in the editor.
     */
    public function imageEditorAction()
    {
        $response = $this->getResponse();
        $request = $this->getRequest();
        $params = $request->getParams();

        $image = $this->_fileManagerCreator->createFileFromBase64($params['id'], $params['image']);
        $result = $this->_raptor->saveImage($image);

        $response->setBody($result);
    }

    /**
     * Render an image.
     *
     * Used by the file manager to render images.
     *
     * @param $filename
     * @param bool $thumbnail
     */
    protected function _renderImage($filename, $thumbnail = false)
    {
        $path = $this->_raptor->getStoragePath($filename, $thumbnail);

        $image = Varien_Image_Adapter::factory('GD2');
        $image->open($path);
        $image->display();
    }
}