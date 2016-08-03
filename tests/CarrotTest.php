<?php

namespace Sunspikes\Carrot\Tests;

use Sunspikes\Carrot\Carrot;
use Mockery as M;

class CarrotTest extends \PHPUnit_Framework_TestCase
{
    /** @var Carrot */
    private $queue;

    private static $exchange = 'test-exchange';
    private static $type = 'direct';

    private static $config = [
        'host' => '127.0.0.1',
        'port' => 5672,
        'username' => 'guest',
        'password' => 'guest',
        'vhost' => '/'
    ];

    public function setUp()
    {
        $this->queue = new Carrot(self::$exchange, self::$type, self::$config);

        parent::setUp();
    }

    public function testProducer()
    {
        $this->assertInstanceOf('\Sunspikes\Carrot\Producer\Producer', $this->queue->getProducer());
    }

    public function testConsumer()
    {
        $this->assertInstanceOf('\Sunspikes\Carrot\Consumer\Consumer', $this->queue->getConsumer());
    }

    /**
     * @expectedException \Sunspikes\Carrot\Exception\ConnectionException
     */
    public function testQueueConnectionException()
    {
        $config = array_merge(self::$config, [
            'username' => 'some-wrong-username',
            'password' => 'some-wrong-password',
        ]);

        new Carrot(self::$exchange, self::$type, $config);
    }
}