<?php

namespace Sunspikes\Carrot\Tests;

use Sunspikes\Carrot\Producer\Producer;
use Mockery as M;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    private $exchange;

    public function setUp()
    {
        $this->exchange = 'test-exchange';

        parent::setUp();
    }

    public function testAdd()
    {
        $consumer = $this->buildProducer();
        $consumer->send('test', []);
    }

    /**
     * @expectedException \Sunspikes\Carrot\Exception\ProducerException
     */
    public function testAddException()
    {
        $consumer = $this->buildProducer(true);
        $consumer->send('test', []);
    }

    private function buildProducer($withException = false)
    {
        $channel = M::mock('\PhpAmqpLib\Channel\AMQPChannel');

        if ($withException) {
            $channel->shouldReceive('basic_publish')->once()->andThrow('\Exception');
        } else {
            $channel->shouldReceive('basic_publish')->once()->andReturnNull();
        }

        return new Producer($channel, $this->exchange);
    }
}