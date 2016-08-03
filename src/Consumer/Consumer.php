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

use Sunspikes\Carrot\CarrotConnectionTrait;
use Sunspikes\Carrot\Exception\ConsumerException;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Queue consumer
 */
class Consumer implements ConsumerInterface
{
    use CarrotConnectionTrait;

    protected $channel;
    protected $exchange;

    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     * @internal param DriverInterface $driver
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
            $this->channel->queue_declare($name, false, true, false, false);
            $this->channel->queue_bind($name, $this->exchange, $name);
            $this->channel->basic_consume($name, '', false, false, false, false, function ($message) use ($name, $callable) {
                $data = $this->decodeQueueMessage($message);
                call_user_func($callable, $data);
                $this->sendAcknowledgment($message->delivery_info['delivery_tag']);
            });
        } catch (\Exception $e) {
            throw new ConsumerException('Carrot consumer failed to add service: '. $e->getMessage());
        }
    }

    /**
     * Decode the message and return array
     *
     * @param $message
     * @return array
     */
    private function decodeQueueMessage($message)
    {
        return json_decode($message->body, true);
    }

    /**
     * After processing completed, Send ACK
     *
     * @param string $deliveryTag
     */
    private function sendAcknowledgment($deliveryTag)
    {
        $this->channel->basic_ack($deliveryTag);
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
