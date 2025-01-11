# Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require kim1ne/socket-server
```

The library uses libraries of the ReactPHP for async. Stream locks

### Supported protocols
- WS - see [RFC6455](https://datatracker.ietf.org/doc/html/rfc6455)
- WSS
- TCP
- UDP
- TLS

### Usage
- [Create server](https://github.com/kim1ne/socket-server/tree/main?tab=readme-ov-file#create-server-and-run)
- [Example: create server TLS/WSS](https://github.com/kim1ne/socket-server/tree/main?tab=readme-ov-file#example-create-server-tlswss)


#### Create server and run

```php
use Kim1ne\InputMessage;
use Kim1ne\Socket\Server\Connection;
use Kim1ne\Socket\Server\Message;
use Kim1ne\Socket\Server\Server;
use Kim1ne\Socket\Server\Transport;

$server = new Server(transport: Transport::WS, port: 2346);

$server->on('connection', function (Connection $connection, Server $server) {
    InputMessage::green('Connected!')
});

$server->on('message', function (Message $message, Connection $connection, Server $server) {
    InputMessage::green("I've got the message!");
});

$server->on('close', function (Server $server) {
    InputMessage::green('Disconnected')
});

$server->on('error', function (\Throwable $throwable) {
    InputMessage::red('Error: ' . $throwable->getMessage());
});

$server->run();
```

#### Example: create server TLS/WSS
```php
use Kim1ne\Socket\Server\Server;
use Kim1ne\Socket\Server\Transport;

$server = new Server(transport: Transport::TLS, port: 2346, serverContext: [
        "ssl" => [
        "local_cert" => "./certs/server.crt",
        "local_pk" => "./certs/server.key",
        "verify_peer" => false,
        "crypto_method" => STREAM_CRYPTO_METHOD_TLSv1_2_SERVER | STREAM_CRYPTO_METHOD_TLSv1_3_SERVER,
        "disable_compression" => true,
    ]
]);
```
