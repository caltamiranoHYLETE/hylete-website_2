<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\Test\MockTrait;

class HandleActionMock implements Common\iHandleAction {

    use MockTrait;

    public function handleAction($Request){
        if ($this->isMethodReturnExist(__FUNCTION__)) {
            throw new \Exception('Test exception');
        }
        return new Response\Order(true, null, '1234', '1234');
    }

}