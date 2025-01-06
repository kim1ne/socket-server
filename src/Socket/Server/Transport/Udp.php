<?php

namespace Kim1ne\Socket\Server\Transport;

use Kim1ne\Socket\Server\Connection;
use Kim1ne\Socket\Server\Message;
use Kim1ne\Socket\Server\Server;

#[\AllowDynamicProperties]
class Udp extends Transport
{
    /**
     * @var resource $server
     */
    private $server;

    public function getProtocol(): string
    {
        return 'udp';
    }

    public function connection($message): Connection
    {
        $connection =  new Connection(
            $message,
            $this
        );

        if ($this->context->getClientAddress() !== null) {
            $connection->set('clientAddress', $this->context->getClientAddress());
        }

        return $connection;
    }

    public function send(Connection $connection, Message $message): void
    {
        stream_socket_sendto($this->server, $message, 0, $this->context->getClientAddress());
    }

    public function createServer()
    {
        $server = stream_socket_server(
            $this->getListenAddress(),
            $error,
            $errstr,
            STREAM_SERVER_BIND
        );

        if ($server === false) {
            throw new \Exception("Error: $error ($errstr)\n");
        }

        $this->server = $server;

        return $server;
    }

    public function readServer(Server $server)
    {
        $message = stream_socket_recvfrom($server->getStreamServer(), 1500, 0, $clientAddress);

        if ($message === false) {
            return false;
        }

        $this->context->setClientAddress($clientAddress);

        return $message;
    }

    public function readClient($clientSocket): string|false
    {
        return $clientSocket;
    }

    public function permanentConnection(): bool
    {
        return false;
    }
}