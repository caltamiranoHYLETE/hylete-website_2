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
 * @category    Icommerce
 * @package     Icommerce_BlockMessages
 * @author      Allan Paiste <allan.paiste@vaimo.com>
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Icommerce_BlockMessages_Test_Model_FeedTest extends Icommerce_BlockMessages_Test_Case
{
    public function testFeedCheckUpdateShouldNotFetchFeedData()
    {
        $feedMock = $this->_getModelMock('adminnotification/feed', array('getFeedData', 'getLastUpdate', 'setLastUpdate'));
        $feedMock->expects($this->never())->method('getFeedData');

        $feedMock->checkUpdate();
    }
}