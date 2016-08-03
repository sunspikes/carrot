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

use Sunspikes\Carrot\Consumer\ConsumerInterface;
use Sunspikes\Carrot\Exception\CarrotException;
use Sunspikes\Carrot\Producer\ProducerInterface;

/**
 * Queue builder interface
 */
interface QueueInterface
{
    /**
     * Get producer
     *
     * @return ProducerInterface
     */
    public function getProducer();

    /**
     * Get consumer
     *
     * @return ConsumerInterface
     */
    public function getConsumer();

    /**
     * A facade to create producer
     *
     * @param string $exchange
     * @param string $exchangeType
     * @return ProducerInterface
     * @throws CarrotException
     */
    public static function producer($exchange, $exchangeType = 'direct');

    /**
     * A facade to create consumer
     *
     * @param string $exchange
     * @param string $exchangeType
     * @return ConsumerInterface
     * @throws CarrotException
     */
    public static function consumer($exchange, $exchangeType = 'direct');
}