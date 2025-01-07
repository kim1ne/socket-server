<?php

namespace Kim1ne\Socket\Server;

use Kim1ne\Socket\Message;
use Kim1ne\Socket\Server\Transport\TransportInterface;

class Connection
{
    private array $properties = [];

    /**
     * @param resource|string $socket
     * @param TransportInterface $transport
     */
    public function __construct(
        private                             $socket,
        private readonly TransportInterface $transport
    ) {}

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    public function __get(string $name)
    {
        return $this->transport->getContext()->$name;
    }

    public function set(string $name, $value): static
    {
        $this->properties[$name] = $value;
        return $this;
    }

    public function get(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    public function send(string|Message $message): void
    {
        $this->transport->send($this, $message);
    }

    public function getId(): string
    {
        return is_resource($this->socket) ? (int) $this->socket : $this->transport->getContext()->getClientAddress();
    }

    /**
     * @return resource|string
     */
    public function getSocket()
    {
        return $this->socket;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }
}