Carrot
============

[![Latest Stable Version](https://poser.pugx.org/sunspikes/carrot/v/stable)](https://packagist.org/packages/sunspikes/carrot)
[![License](https://poser.pugx.org/sunspikes/carrot/license)](https://packagist.org/packages/sunspikes/carrot)

A simple abstraction for RabbitMQ.

Carrot aims to make it easy to get started with RabbitMQ and PHP, at the same time maintaining the flexibility to implement all the supported messaging patterns.

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

## Testing

Run `phpunit`

## Author

Krishnaprasad MG [@sunspikes]

## Contributing

Please feel free to send pull requests.

## License

This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).