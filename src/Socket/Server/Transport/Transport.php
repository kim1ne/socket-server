<?php

namespace Kim1ne\Socket\Server\Transport;

use Kim1ne\Socket\Server\Connection;
use Kim1ne\Socket\Server\Context;
use Kim1ne\Socket\Server\Message;
use Kim1ne\Socket\Server\Server;
use React\EventLoop\LoopInterface;

abstract class Transport implements TransportInterface
{
    public function __construct(
        public readonly string $host,
        public readonly int $port
    ) {}

    protected ?Context $context = null;

    public function connection($socket): Connection
    {
        return new Connection($socket, $this);
    }

    public function createServer()
    {
        $server = stream_socket_server($this->getListenAddress(), $errno, $error);

        if ($server === false) {
            throw new \Exception("Error: $error ($errno)\n");
        }

        return $server;
    }

    public function getListenAddress(): string
    {
        return $this->getProtocol(). "://$this->host:$this->port";
    }

    public function readServer(Server $server)
    {
        $clientSocket = stream_socket_accept($server->getStreamServer(), 0);

        if ($clientSocket === false) {
            return false;
        }

        stream_set_blocking($clientSocket, false);

        return $clientSocket;
    }

    public function readClient($clientSocket): string|false
    {
        return fread($clientSocket, 1024);
    }

    public function setContext(Context $context): static
    {
        if ($this->context === null) {
            $this->context = $context;
        }

        return $this;
    }

    public function getContext(): Context
    {
        if ($this->context === null) {
            throw new \Exception("Context is not set. Please set it before using.");
        }

        return $this->context;
    }

    public function isEncode(): bool
    {
        return $this->getContext()->messageEncode  ?? true;
    }

    public function send(Connection $connection, Message $message): void
    {
        if ($this->isEncode()) {
            $message = $this->encode($message);
        }

        fwrite($connection->getSocket(), $message);
    }

    public function prepare($socket, LoopInterface $loop, callable $onComplete): void
    {
        $onComplete(true);
    }

    public function decode(string $buffer): string
    {
        return $buffer;
    }

    public function encode(string $buffer): string
    {
        return $buffer;
    }

    public function permanentConnection(): bool
    {
        return true;
    }
}