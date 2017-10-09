<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\API\Processors;
use GlobalE\Test\MockTrait;

class GetOrdersVATInvoiceMock extends Processors\GetOrdersVATInvoice
{
    use MockTrait;

    public function formatParameters(array $Uri){

        return parent::formatParameters($Uri);
    }

}