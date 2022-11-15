<?php
namespace vk\lib\messenger;

class Message
{
    private string $queue;
    private array $headers;
    private object $payload;

    public function __construct(string $queue, object $payload, array $headers = [])
    {
        $this->queue = $queue;
        $this->payload = $payload;
        $this->headers = $headers;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getPayload(): object
    {
        return $this->payload;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
