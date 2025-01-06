<?php

namespace Kim1ne\Socket;

class Message
{
    public function __construct(
        private string|array|\JsonSerializable $data
    ) {}

    public function __toString(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        return $this->toJson();
    }

    public function getData(): string|array|\JsonSerializable
    {
        return $this->data;
    }

    public function getDecodeJson(bool $asArray = true): ?string
    {
        if (is_string($this->data)) {
            return json_decode($this->data, $asArray);
        }

        return null;
    }

    public function toJson(int $flag = JSON_UNESCAPED_UNICODE): string
    {
        $string = json_encode($this->data, $flag);

        if ($string === false) {
            return '';
        }

        return $string;
    }
}