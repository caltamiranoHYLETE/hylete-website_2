<?php
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Response;

class HandleCreateStub implements Common\iHandleAction {

    public function handleAction($Request){
        return new Response\Order(true, null, '1234578', '1234');
    }
}