<?php

require __DIR__. '/../vendor/autoload.php';

use Sunspikes\Carrot\Carrot;

// Initialize a rabbitmq connection configuration
Carrot::config([
    'host' => '127.0.0.1',
    'port' => 5672,
    'username' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
]);

// Create a consumer which reads from 'test-exchange'
$consumer = Carrot::consumer('test-exchange');

// Add a listener for for queue 'TestQueue', the callback will be executed
// everytime there is a new message on this queue, which will print 'hello'
$consumer->add('TestQueue', function($message) {
    print "\n[*] Received message: ". $message->text;
});

print "\n[*] Consumer starting to listen for messages from rabbitmq...";

// Listen for messages on 'test-exchange', this is a wait loop which will keep the
// consumer running till it's manually terminated or the connection is lost
$consumer->listen('test-exchange');
