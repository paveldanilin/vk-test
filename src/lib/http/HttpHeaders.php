<?php
namespace vk\lib\http;

final class HttpHeaders
{
    private array $headers;

    public function __construct(array $headers)
    {
        $this->setHeaders($headers);
    }

    public function all(): array
    {
        return $this->headers;
    }

    public function keys(): array
    {
        return \array_keys($this->headers);
    }

    public function getFirst(string $name, $default = null): ?string
    {
        $v = $this->headers[\strtolower($name)] ?? $default;
        if (\is_array($v)) {
            return $v[0] ?? $default;
        }
        return $v;
    }

    public function getValues(string $name): array
    {
        $v = $this->headers[\strtolower($name)] ?? [];
        if (\is_array($v)) {
            return $v;
        }
        return [$v];
    }

    public function has(string $name): bool
    {
        return \array_key_exists(\strtolower($name), $this->headers);
    }

    public function getContentType(): string
    {
        return $this->getFirst('content-type', 'text');
    }

    public function getAccept(): string
    {
        return $this->getFirst('accept', '*/*');
    }

    private function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            if (\strpos($value, ',') !== false) {
                $value = \explode(',', $value);
            }
            $this->headers[\strtolower($name)] = $value;
        }
    }
}
