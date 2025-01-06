<?php

namespace Kim1ne\Socket\Server\Transport;

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

    public function createServer()
    {
        $context = $this->context->server;

        if ($context === null) {
            throw new \Exception('Set contextServer');
        }

        unset($this->context->server);

        $server = stream_socket_server(
            $this->getListenAddress(),
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