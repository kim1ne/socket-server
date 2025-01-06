<?php

namespace Kim1ne\Socket\Transport;

class Tcp extends Transport
{
    public function getProtocol(): string
    {
        return 'tcp';
    }
}