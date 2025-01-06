<?php

namespace Kim1ne\Socket\Transport;

class Wss extends Websocket
{
    public function getProtocol(): string
    {
        return 'tls';
    }

    public function createServer(string $host, int $port)
    {
        return (new Tls())->setContext($this->context)->createServer($host, $port);
    }
}