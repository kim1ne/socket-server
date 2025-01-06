<?php

namespace Kim1ne\Socket\Server\Transport;

class Tcp extends Transport
{
    public function getProtocol(): string
    {
        return 'tcp';
    }
}