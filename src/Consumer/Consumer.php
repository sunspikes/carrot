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

namespace Sunspikes\Carrot\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Sunspikes\Carrot\CarrotConnectionTrait;
use Sunspikes\Carrot\ConfigAwareTrait;
use Sunspikes\Carrot\Exception\ConsumerException;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Queue consumer
 */
class Consumer implements ConsumerInterface
{
    use CarrotConnectionTrait,
        ConfigAwareTrait;

    protected $channel;
    protected $exchange;

    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     */
    public function __construct(AMQPChannel $channel, $exchange)
    {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     */
    public function add($name, callable $callable)
    {
        try {
            $config = $this->config;
            $this->channel->queue_declare(
                $name,
                $config['queue']['passive'],
                $config['queue']['durable'],
                $config['queue']['exclusive'],
                $config['queue']['auto_delete'],
                $config['queue']['no_wait'],
                $config['queue']['arguments'],
                $config['queue']['ticket']
            );
            $this->channel->queue_bind(
                $name,
                $this->exchange,
                $name,
                $config['queue']['no_wait'],
                $config['queue']['arguments'],
                $config['queue']['ticket']
            );
            $this->channel->basic_consume(
                $name,
                $config['consumer']['consumer_tag'],
                $config['consumer']['no_local'],
                $config['consumer']['no_ack'],
                $config['consumer']['exclusive'],
                $config['consumer']['no_wait'],
                function (AMQPMessage $message) use ($callable, $config) {
                    $originalMessage = $message;

                    if (true === $config['delegate']) {
                        $message = $this->decodeMessage($originalMessage);
                    }

                    call_user_func($callable, $message);

                    if (true === $config['delegate']) {
                        $this->acknowledgeMessage($originalMessage);
                    }
                },
                $config['consumer']['ticket'],
                $config['consumer']['arguments']
            );
        } catch (\Exception $e) {
            throw new ConsumerException('Carrot consumer failed to add service: '. $e->getMessage());
        }
    }

    /**
     * Decode the message and return array
     *
     * @param AMQPMessage $message
     * @return array
     */
    public function decodeMessage(AMQPMessage $message)
    {
        return json_decode($message->body);
    }

    /**
     * Send ACK for the message
     *
     * @param AMQPMessage $message
     */
    public function acknowledgeMessage(AMQPMessage $message)
    {
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }

    /**
     * @param AMQPMessage $message
     * @param bool $requeue
     */
    public function rejectMessage(AMQPMessage $message, $requeue = false)
    {
        $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], $requeue);
    }

    /**
     * @inheritdoc
     */
    public function listen()
    {
        try {
            while(count($this->channel->callbacks)) {
                if (! $this->channel->getConnection()->isConnected()) {
                    throw new ConsumerException('Carrot consumer disconnected');
                }
                $this->channel->wait(null, true);
            }

            $this->closeConnection();
        } catch (\Exception $e) {
            // consumer failed to listen or disconnected
            throw new ConsumerException("Carrot lost connection with RabbitMQ server");
        }
    }
}

