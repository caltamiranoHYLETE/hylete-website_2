<?php

class Vaimo_IntegrationBase_Test_Model_Process extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test that the process gets queued when process queue is called
     *
     * @test
     */
    public function testThatQueueAddIsCalled()
    {
        // Mock queue - expects to be run once
        $queue = $this->getMock('mock_queue', array('add'));
        $queue->expects($this->once())
            ->method('add')
            ->will($this->returnSelf());

        // Mock out method that returns the queue and return mock queue
        $process = $this->getModelMock('integrationbase/process', array('getQueue'));
        $process->expects($this->any())
            ->method('getQueue')
            ->will($this->returnValue($queue));

        // Assing mock class on process model
        $this->replaceByMock('model', 'integrationbase/process', $process);

        // Queue a process without any arguments and standard code
        Mage::getModel('integrationbase/process')->queue();
    }
}