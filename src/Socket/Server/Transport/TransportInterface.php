<?php

namespace Kim1ne\Socket\Server\Transport;

use Kim1ne\Socket\Server\Connection;
use Kim1ne\Socket\Server\Context;
use Kim1ne\Socket\Message;
use Kim1ne\Socket\Server\Server;
use React\EventLoop\LoopInterface;

interface TransportInterface
{
    public function __construct(string $host, int $port);
    public function getListenAddress(): string;
    public function setContext(Context $context);
    public function getContext(): Context;
    public function getProtocol(): string;

    public function decode(string $buffer): string;

    public function encode(string $buffer): string;

    public function send(Connection $connection, Message|string $message): void;

    /**
     * @param resource $socket
     * @param LoopInterface $loop
     * @param callable $onComplete
     * @return void
     */
    public function prepare($socket, LoopInterface $loop, callable $onComplete): void;

    public function createServer();

    public function readServer(Server $server);

    public function connection($socket): Connection;

    public function readClient($clientSocket): string|false;

    public function permanentConnection(): bool;
}
