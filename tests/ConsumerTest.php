<?php

namespace Sunspikes\Carrot\Tests;

use Sunspikes\Carrot\Consumer\Consumer;
use Mockery as M;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    private $exchange;

    public function setUp()
    {
        $this->exchange = 'test-exchange';

        parent::setUp();
    }

    public function testAdd()
    {
        $consumer = $this->buildAddConsumer();
        $consumer->add('test', function () {});
    }

    /**
     * @expectedException \Sunspikes\Carrot\Exception\ConsumerException
     */
    public function testAddException()
    {
        $consumer = $this->buildAddConsumer(true);
        $consumer->add('test', function () {});
    }

    public function testListenWait()
    {
        $consumer = $this->buildListenConsumer(true);
        $consumer->listen();
    }

    public function testListenNoWait()
    {
        $consumer = $this->buildListenConsumer();
        $consumer->listen();
    }

    /**
     * @expectedException \Sunspikes\Carrot\Exception\ConsumerException
     */
    public function testListenException()
    {
        $consumer = $this->buildListenConsumer(false, true);
        $consumer->listen();
    }

    private function buildAddConsumer($withException = false)
    {
        $channel = M::mock('\PhpAmqpLib\Channel\AMQPChannel');
        $channel->shouldReceive('queue_declare')->once()->andReturnNull();
        $channel->shouldReceive('queue_bind')->once()->andReturnNull();

        if ($withException) {
            $channel->shouldReceive('basic_consume')->once()->andThrow('\Exception');
        } else {
            $channel->shouldReceive('basic_consume')->once()->andReturnNull();
        }

        return new Consumer($channel, $this->exchange);
    }

    private function buildListenConsumer($withWait = false, $withException = false)
    {
        $connection = M::mock('\PhpAmqpLib\Connection\AMQPStreamConnection');
        $connection->shouldReceive('close')->once()->andReturnNull();

        $channel = M::mock('\PhpAmqpLib\Channel\AMQPChannel');
        $channel->shouldReceive('getConnection')->once()->andReturn($connection);

        $callbacks = $withWait ? 1 : 0;
        $channel->shouldReceive('callbacks')->once()->andReturn($callbacks);

        if ($withException) {
            $channel->shouldReceive('close')->once()->andThrow('\Exception');
        } else {
            $channel->shouldReceive('close')->once()->andReturnNull();
        }

        return new Consumer($channel, $this->exchange);
    }
}