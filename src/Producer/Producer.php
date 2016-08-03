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

namespace Sunspikes\Carrot\Producer;

use Sunspikes\Carrot\Exception\ProducerException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Queue producer
 */
class Producer implements ProducerInterface
{
    protected $channel;
    protected $exchange;

    /**
     * @param AMQPChannel $channel
     * @param string $exchange
     * @internal param AMQPChannel $driver
     */
    public function __construct(AMQPChannel $channel, $exchange)
    {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }

    /**
     * Close the connection
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * @inheritdoc
     */
    public function send($name, array $arguments)
    {
        try {
            $message = new AMQPMessage(json_encode($arguments), ['delivery_mode' => 2]);
            $this->channel->basic_publish($message, $this->exchange, $name);
        } catch (\Exception $e) {
            throw new ProducerException('Carrot producer failed to send message: '. $e->getMessage());
        }
    }

    /**
     * Close the current connection
     */
    protected function closeConnection()
    {
        if ($this->channel) {
            $this->channel->close();
            $connection = $this->channel->getConnection();

            if ($connection) {
                $connection->close();
            }
        }
    }
}