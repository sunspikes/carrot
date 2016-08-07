<?php
/**
 * The default rabbitmq configuration
 */

return [
    'host' => '127.0.0.1',
    'port' => 5672,
    'username' => 'guest',
    'password' => 'guest',
    'vhost' => '/',

    // delegate serialization, deserialization and
    // auto acknowledgement of the messages to carrot
    'delegate' => true,
    
    'connection' => [
        'insist' => false,
        'login_method' => 'AMQPLAIN',
        'login_response' => 'null',
        'locale' => 'en_US',
        'timeout' => 60,
        'read_write_timeout' => 60,
        'context' => null,

        // make a persistent connection and disable heartbeat
        // if you want to use heartbeat instead change these
        'keepalive' => true,
        'heartbeat' => 0,
    ],

    'exchange' => [
        'passive' => false,

        // make the exchange durable, so that it survives server restart
        'durable' => true,
        'auto_delete' => false,
        'internal' => false,
        'no_wait' => false,
        'arguments' => null,
        'ticket' => null,
    ],

    'queue' => [
        'passive' => false,

        // make the queue durable, so that it survives server restart
        'durable' => true,
        'exclusive' => false,
        'auto_delete' => false,
        'no_wait' => false,
        'arguments' => null,
        'ticket' => null,
    ],

    'consumer' => [
        'consumer_tag' => '',
        'no_local' => false,
        'no_ack' => false,
        'exclusive' => false,
        'no_wait' => false,
        'ticket' => null,
        'arguments' => [],
    ],
];
