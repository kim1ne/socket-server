<?php

namespace Kim1ne\Socket\Transport;

class Wss extends Websocket
{
    public function getProtocol(): string
    {
        return 'tls';
    }

    public function createServer()
    {
        return (new Tls($this->host, $this->port))->setContext($this->context)->createServer();
    }
}