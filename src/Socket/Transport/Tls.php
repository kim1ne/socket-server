<?php

namespace Kim1ne\Socket\Transport;

class Tls extends Transport
{
    public function getProtocol(): string
    {
        return 'tls';
    }

    public function permanentConnection(): bool
    {
        return true;
    }

    public function createServer(string $host, int $port)
    {
        $context = $this->context->server;

        if ($context === null) {
            throw new \Exception('Set contextServer');
        }

        unset($this->context->server);

        $server = stream_socket_server($this->getListenAddress($host, $port),
            $errno,
            $error,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            stream_context_create($context)
        );

        if ($server === false) {
            throw new \Exception("Error: $error ($errno)\n");
        }

        return $server;
    }
}