<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\API\Processors;
use GlobalE\Test\MockTrait;

/**
 * Class UpdateOrderDispatchMock
 * @package GlobalE\Test\SDK\API\Processors
 */
class UpdateOrderDispatchMock extends Processors\UpdateOrderDispatch
{
    use MockTrait;

    /**
     * @param array $Uri
     * @return string
     */
    public function formatParameters(array $Uri){

        return parent::formatParameters($Uri);
    }

}