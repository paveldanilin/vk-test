<?php
namespace vk\lib\http;

require_once __DIR__ . '/HttpHeaders.php';

final class Request
{
    private string $path;
    private string $method;
    private HttpHeaders $headers;
    private ?string $body;
    private array $query;

    public function __construct(string $path, string $method, array $headers, ?string $body, array $query)
    {
        $this->path = $path;
        $this->method = $method;
        $this->headers = new HttpHeaders($headers);
        $this->body = $body;
        $this->query = $query;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): HttpHeaders
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function hasQueryParam(string $name): bool
    {
        return \array_key_exists($name, $this->query);
    }

    public function getQueryParam(string $name, $default = null): ?string
    {
        return $this->query[$name] ?? $default;
    }
}

