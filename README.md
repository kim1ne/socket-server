# Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require kim1ne/kafka
```

The library uses libraries of the ReactPHP for async. Stream doesn't lock

### Supported protocols
- WS - see [RFC6455](https://datatracker.ietf.org/doc/html/rfc6455)
- WSS
- TCP
- UDP
- TLS

#### Create server
```php
use Kim1ne\Loop\LoopServer;
use Kim1ne\Socket\Connection;
use Kim1ne\Socket\Message;
use Kim1ne\Socket\Server;
use Kim1ne\Socket\Transport;
use Kim1ne\InputMessage;

$server = new Server(transport: Transport::WS, port: 2346);

$server->on('connection', function (Connection $connection, Server $server) {
    InputMessage::green('Connected!')
});

$server->on('message', function (Message $message, Connection $connection, Server $server) {
    InputMessage::green("I've got the message!");
    $server->sendAllButNotToHim($connection, new Message([
        'message' => '1 user connected' 
    ]));
});

$server->on('close', function (Server $server) {
    $server->sendAll(new Message([
        'message' => '1 user disconnected'
    ]));
});

$server->on('error', function (\Throwable $throwable) {
    InputMessage::red('Error: ' . $throwable->getMessage());
});

LoopServer::run($server);
```