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

print "\n[*] Producer started...";

// Create producer which sends messages to 'test-exchange'
$producer = Carrot::producer('test-exchange');

// Send a message to 'TestQueue'
$producer->send('TestQueue', [
    'text' => 'hello',
]);

print "\n[*] Producer sent message to rabbitmq";
