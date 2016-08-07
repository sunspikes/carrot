<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Krishnaprasad MG <sunspikes@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Sunspikes\Carrot;

use Sunspikes\Carrot\Exception\CarrotException;
use Sunspikes\Carrot\Exception\ConnectionException;
use Sunspikes\Carrot\Producer\Producer;
use Sunspikes\Carrot\Consumer\Consumer;
use Sunspikes\Carrot\Consumer\ConsumerInterface;
use Sunspikes\Carrot\Producer\ProducerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Queue builder
 */
class Carrot implements QueueInterface
{
    protected $channel;
    protected $exchange;
    protected $type;
    protected $config;

    /**
     * @param string $exchange
     * @param string $type
     * @param array $config
     * @throws ConnectionException
     */
    public function __construct($exchange, $type = 'direct', $config = [])
    {
        $this->config = $this->buildConfig($config);
        $this->exchange = $exchange;
        $this->type = $type;

        try {
            $connection = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['username'],
                $this->config['password'],
                $this->config['vhost'],
                $this->config['connection']['insist'],
                $this->config['connection']['login_method'],
                $this->config['connection']['login_response'],
                $this->config['connection']['locale'],
                $this->config['connection']['timeout'],
                $this->config['connection']['read_write_timeout'],
                $this->config['connection']['context'],
                $this->config['connection']['keepalive'],
                $this->config['connection']['heartbeat']
            );

            $this->channel = $connection->channel();
            $this->channel->exchange_declare(
                $exchange,
                $type,
                $this->config['exchange']['passive'],
                $this->config['exchange']['durable'],
                $this->config['exchange']['auto_delete'],
                $this->config['exchange']['internal'],
                $this->config['exchange']['no_wait'],
                $this->config['exchange']['arguments'],
                $this->config['exchange']['ticket']
            );
        } catch (\Exception $e) {
            throw new ConnectionException('Carrot failed to build connection: '. $e->getMessage());
        }
    }

    /**
     * Build the connection configuration
     *
     * @param array $config
     * @return array
     */
    protected function buildConfig($config)
    {
        $defaultConfig = require __DIR__ . '/../config/config.php';

        return array_replace_recursive($defaultConfig, $config);
    }

    /**
     * @inheritdoc
     */
    public function getProducer()
    {
        $producer = new Producer($this->channel, $this->exchange);
        $producer->setConfig($this->config);

        return $producer;
    }

    /**
     * @inheritdoc
     */
    public function getConsumer()
    {
        $consumer = new Consumer($this->channel, $this->exchange);
        $consumer->setConfig($this->config);

        return $consumer;
    }

    /**
     * Configure the connection
     *
     * @param array $config
     * @return array
     */
    public static function config($config = [])
    {
        static $connectionConfig;

        if (! empty($config)) {
            $connectionConfig = $config;
        }

        return $connectionConfig;
    }

    /**
     * @inheritdoc
     */
    public static function producer($exchange, $exchangeType = 'direct')
    {
        return static::buildChannel('producer', $exchange, $exchangeType);
    }

    /**
     * @inheritdoc
     */
    public static function consumer($exchange, $exchangeType = 'direct')
    {
        return static::buildChannel('consumer', $exchange, $exchangeType);
    }

    /**
     * Build a producer/consumer channel
     *
     * @param string $type
     * @param string $exchange
     * @param string $exchangeType
     * @return ConsumerInterface|ProducerInterface
     * @throws CarrotException
     */
    public static function buildChannel($type, $exchange, $exchangeType)
    {
        static $channels = [];

        $config = static::config();

        try {

            if (!isset($channels[$type][$exchange])) {
                $queue = new static($exchange, $exchangeType, $config);
                $getMethod = 'get' . ucfirst($type);
                $channels[$type][$exchange] = $queue->$getMethod();
            }
        } catch (\Exception $e) {
            throw new CarrotException("Error in creating rabbitmq channel:" . $e->getMessage());
        }

        return $channels[$type][$exchange];
    }
}
