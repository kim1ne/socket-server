<?php

namespace Kim1ne\Socket;

use Kim1ne\Socket\Transport\Tcp;
use Kim1ne\Socket\Transport\Tls;
use Kim1ne\Socket\Transport\TransportInterface;
use Kim1ne\Socket\Transport\Udp;
use Kim1ne\Socket\Transport\Websocket;
use Kim1ne\Socket\Transport\Wss;

enum Transport: string
{
    case WS = Websocket::class;
    case WSS = Wss::class;
    case TCP = TCP::class;
    case UDP = UDP::class;
    case TLS = TLS::class;

    public function get(string $host, int $port): TransportInterface
    {
        return new $this->value($host, $port);
    }
}
