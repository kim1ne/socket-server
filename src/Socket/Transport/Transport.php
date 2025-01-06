<?php

namespace Kim1ne\Socket\Transport;

use Kim1ne\Socket\Connection;
use Kim1ne\Socket\Context;
use Kim1ne\Socket\Message;
use Kim1ne\Socket\Server;
use React\EventLoop\LoopInterface;

abstract class Transport implements TransportInterface
{
    protected ?Context $context = null;

    public function connection($socket): Connection
    {
        return new Connection($socket, $this);
    }

    public function createServer(string $host, int $port)
    {
        $server = stream_socket_server($this->getListenAddress($host, $port), $errno, $error);

        if ($server === false) {
            throw new \Exception("Error: $error ($errno)\n");
        }

        return $server;
    }

    public function getListenAddress(string $host, int $port): string
    {
        return $this->getProtocol(). "://$host:$port";
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