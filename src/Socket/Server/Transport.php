<?php

namespace Kim1ne\Socket\Server;

use Kim1ne\Socket\Server\Transport\Tcp;
use Kim1ne\Socket\Server\Transport\Tls;
use Kim1ne\Socket\Server\Transport\TransportInterface;
use Kim1ne\Socket\Server\Transport\Udp;
use Kim1ne\Socket\Server\Transport\Websocket;
use Kim1ne\Socket\Server\Transport\Wss;

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
