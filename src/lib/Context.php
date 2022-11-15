<?php

namespace vk\lib;

final class Context
{
    private array $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function hasValue(string $name): bool
    {
        return \array_key_exists($name, $this->context);
    }

    public function getValue(string $name, $default = null)
    {
        return $this->context[$name] ?? $default;
    }

    public function getArray(string $name): array
    {
        $v = $this->getValue($name);
        if (null === $v) {
            return [];
        }
        if (\is_array($v)) {
            return $v;
        }
        return [$v];
    }

    public function setValue(string $name, $value): void
    {
        $this->context[$name] = $value;
    }

    public function resolveHost(string $resource, string $default_host = 'localhost', string $id_key = 'user_id'): string
    {
        if (!$this->hasValue($id_key)) {
            return $default_host;
        }

        $pool = $this->getArray('config')[$resource]['pool'] ?? [];
        $pool_size = \count($pool);
        if (0 === $pool_size) {
            return $default_host;
        }

        $user_id = (int)$this->getValue($id_key);
        $hid = $user_id % $pool_size;

        return $pool[$hid];
    }
}
