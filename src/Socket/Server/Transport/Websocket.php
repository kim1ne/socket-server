<?php

namespace Kim1ne\Socket\Server\Transport;

use React\EventLoop\LoopInterface;

class Websocket extends Transport
{
    public function getProtocol(): string
    {
        return 'tcp';
    }

    public function decode(string $buffer): string
    {
        $secondByte = ord($buffer[1]);
        $len = $secondByte & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } elseif ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }

        $dataLength = strlen($data);
        $masks = str_repeat($masks, (int)floor($dataLength / 4)) . substr($masks, 0, $dataLength % 4);
        return $data ^ $masks;
    }

    public function encode(string $buffer): string
    {
        $length = strlen($buffer);

        if ($length <= 125) {
            $frameHead = chr(0x81);
            $frameHead .= chr($length);
        } elseif ($length >= 126 && $length <= 65535) {
            $frameHead = chr(0x81);
            $frameHead .= chr(126);
            $frameHead .= pack('n', $length);
        } else {
            $frameHead = chr(0x81);
            $frameHead .= chr(127);
            $frameHead .= pack('J', $length);
        }

        $frameHead .= $buffer;

        return $frameHead;
    }

    public function prepare($socket, LoopInterface $loop, callable $onComplete): void
    {
        $buffer = '';

        $loop->addReadStream($socket, function ($socket) use (&$buffer, $loop, $onComplete) {
            $data = fread($socket, 1024);

            if ($data === false || feof($socket)) {
                $loop->removeReadStream($socket);
                $onComplete(false);
                return;
            }

            $buffer .= $data;

            if (!str_contains($buffer, "\r\n\r\n")) {
                return;
            }

            $loop->removeReadStream($socket);

            $onComplete($this->handshake($socket, $buffer));
        });
    }

    /**
     * @param resource $client
     * @return void
     */
    private function handshake($client, string $request): bool
    {
        $match = [];

        preg_match("/Sec-WebSocket-Key: *(.*?)\r\n/i", $request, $match);

        $SecWebSocketKey = $match[1];

        if (empty($SecWebSocketKey)) {
            return false;
        }

        $newKey = base64_encode(sha1($SecWebSocketKey . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));

        $handshakeMessage = "HTTP/1.1 101 Switching Protocol\r\n"
            . "Upgrade: websocket\r\n"
            . "Sec-WebSocket-Version: 13\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Accept: " . $newKey . "\r\n"
            . "\r\n";

        return is_int(fwrite($client, $handshakeMessage));
    }
}