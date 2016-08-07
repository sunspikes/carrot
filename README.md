Carrot
============

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8a5e6274-af30-4606-8a5a-2b514ddace8e/mini.png)](https://insight.sensiolabs.com/projects/8a5e6274-af30-4606-8a5a-2b514ddace8e)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunspikes/carrot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/carrot/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunspikes/carrot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunspikes/carrot/?branch=master)
[![Code Climate](https://codeclimate.com/github/sunspikes/carrot/badges/gpa.svg)](https://codeclimate.com/github/sunspikes/carrot)
[![Build Status](https://travis-ci.org/sunspikes/carrot.svg?branch=master)](https://travis-ci.org/sunspikes/carrot)
[![Latest Stable Version](https://poser.pugx.org/sunspikes/carrot/v/stable)](https://packagist.org/packages/sunspikes/carrot)
[![License](https://poser.pugx.org/sunspikes/carrot/license)](https://packagist.org/packages/sunspikes/carrot)

A simple abstraction for RabbitMQ.

Carrot aims to make it easy to get started with RabbitMQ and PHP, at the same time maintaining the flexibility to implement all the supported messaging patterns.

## Requirements

You must have a rabbitmq server running to use this package. For more on this refer [RabbitMQ documentation](https://www.rabbitmq.com/download.html).

## Installation

With Composer

```
$ composer require sunspikes/carrot
```

## Usage

To configure a RabbitMQ connection

```
Carrot::config([  
    'host' => '127.0.0.1',
    'port' => 5672,
    'username' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
]);
```

To publish message to RabbitMQ,

```
// Create producer which sends messages to 'test-exchange'
$producer = Carrot::producer('test-exchange');

// Send a message to 'TestQueue'
$producer->send('TestQueue', [
    'text' => 'hello',
]);
```

To Consume messages from RabbitMQ

```
// Create a consumer which reads from 'test-exchange'
$consumer = Carrot::consumer('test-exchange');

// Add a listener for for queue 'TestQueue', the callback will be executed
// everytime there is a new message on this queue, which will print 'hello'
$consumer->add('TestQueue', function($message) {
    print $message->text;
});

// Listen for messages on 'test-exchange', this is a wait loop which will keep the
// consumer running till it's manually terminated or the connection is lost
$consumer->listen('test-exchange');
```

## More on usage

You don't have to use static methods to build and use producers and consumers, also the library makes it easy to switch between different exchange types.

For example, you can also create a direct exchange & produce messages to it like,

```
$config = [
    'host' => '127.0.0.1',
    'port' => 5672,
    'username' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
];

$carrot = new Carrot('test-exchange', 'direct', $config);
$producer = $carrot->getProducer();

$producer->send('TestQueue', [
    'text' => 'hello'
]);
```

Also the consumer can be created and listened to a direct exchange for messages like,

```
$carrot = new Carrot('test-exchange', 'direct', $config);
$consumer = $carrot->getConsumer();

$consumer->add('TestQueue', function($message) {
    print $message->text;
});

$consumer->listen('test-exchange');
```

By default carrot producer will automatically serialize the message and consumer will deserialize the message acknowledge the incoming messages, you could disable this delegation by setting the configuration parameter `delegate` to `false`

```
// make some message
$message = ['text' => 'hello'];

// serialize it manually
$message = json_encode($message);

// send the message
$producer->send('TestQueue', $message);

// At consumer end, handle the message manually
$consumer->add('TestQueue', function (AMQPMessage $message) use ($consumer) {
    $decoded = json_decode($message->body);
    
    //... do something with $decoded->text
    
    // if everything went fine acknowledge message
    $consumer->acknowledgeMessage($message);
    
    // if something went wrong with the message reject message, this will discard the message (optionally requeue the message)
    $consumer->rejectMessage($message, $requeue = false);
});

```

## Example

You can run the examples from the /example folder

First run the consumer, it will create the exchange and queue on the rabbitmq server

```
$ php example/consumer.php

[*] Consumer starting to listen for messages from rabbitmq...
[*] Received message: hello
```

As soon as you run the producer, the consumer will print the message text sent by the producer

```
$ php example/producer.php

[*] Producer started...
[*] Producer sent message to rabbitmq
```

## Testing

Run `phpunit`

## Author

Krishnaprasad MG [@sunspikes]

## Contributing

Please feel free to send pull requests.

## License

This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
