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

use Sunspikes\Carrot\AMQPConnectionTrait;
use Sunspikes\Carrot\ConfigAwareTrait;
use Sunspikes\Carrot\Exception\ProducerException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Queue producer
 */
class Producer implements ProducerInterface, AMQPProducerInterface
{
    use AMQPConnectionTrait,
        ConfigAwareTrait;
    
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
     * @inheritdoc
     */
    public function send($name, $arguments)
    {
        try {
            if (true === $this->config['delegate']) {
                $arguments = $this->encodeMessage($arguments);
            }
            
            $message = new AMQPMessage($arguments, $this->config['producer']);
            $this->channel->basic_publish($message, $this->exchange, $name);
        } catch (\Exception $e) {
            throw new ProducerException('Carrot producer failed to send message: '. $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function encodeMessage($arguments)
    {
        return json_encode($arguments);
    }
}
