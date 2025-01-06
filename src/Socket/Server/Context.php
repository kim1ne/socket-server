<?php

namespace Kim1ne\Socket\Server;

#[\AllowDynamicProperties]
class Context
{
    public bool $messageEncode = true;

    public function __set(string $name, $value): void
    {
        $this->$name = $value;
    }

    public function __get(string $name)
    {
        return $this->$name ?? null;
    }

    public function setClientAddress(string $address): static
    {
        $this->clientAddress = $address;
        return $this;
    }

    public function getClientAddress(): ?string
    {
        return $this->clientAddress;
    }
}