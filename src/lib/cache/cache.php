<?php
namespace vk\lib\cache;

use vk\lib\Context;
use function vk\lib\util\uuid4_create;

function cache_key(Context $context, string $ns = '', string $id_var = 'user_id'): string
{
    if (!$context->hasValue($id_var)) {
        return uuid4_create($ns);
    }
    return $context->getValue($id_var) . '_' . $ns;
}

function cache_get_conn(Context $context): \Memcached
{
    $host = $context->resolveHost('memcached');

    $mc = new \Memcached();
    $mc->addServer($host, 11211); // TODO: config

    return $mc;
}

function cache_get(string $key, Context $context): ?string
{
    $conn = cache_get_conn($context);
    $v = $conn->get($key);
    $conn->quit();
    if (false === $v) {
        return null;
    }
    return $v;
}

function cache_get_json(string $key, Context $context): ?array
{
    $v = cache_get($key, $context);
    if (null === $v) {
        return null;
    }
    return \json_decode($v, true, 255, JSON_THROW_ON_ERROR);
}

function cache_set(string $key, $value, Context $context, int $ttl = 0): bool
{
    $conn = cache_get_conn($context);
    $ret = $conn->set($key, $value, $ttl);
    $conn->quit();
    return $ret;
}

function cache_set_json(string $key, $value, Context $context, int $ttl = 0): bool
{
    return cache_set($key, \json_encode($value, JSON_THROW_ON_ERROR), $context, $ttl);
}

function cache_gets(array $keys, Context $context): array
{
    $conn = cache_get_conn($context);
    $ret = $conn->getMulti($keys);
    $conn->quit();
    return $ret;
}

function cache_del(string $key, Context $context): void
{
    $conn = cache_get_conn($context);
    $conn->delete($key);
    $conn->quit();
    return;
}

function cache_get_all(array $keys, Context $context): array
{
    $connections = [];
    $pool = $context->getArray('config')['memcached']['pool'] ?? [];
    $all = [];
    foreach ($pool as $host) {
        if (!\array_key_exists($host, $connections)) {
            $mc = new \Memcached();
            $mc->addServer($host, 11211);
            $connections[$host] = $mc;
        }
        $values = $connections[$host]->getMulti($keys);
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $all[$k] = $v;
            }
        }
    }
    foreach ($connections as $conn) {
        $conn->quit();
    }
    return $all;
}

/*
function cache_val_to_array(string $value, array $columns): array
{
    $rows = \explode("\n", $value, 1000);
    $ret = [];

    foreach ($rows as $row) {
        if (empty($row)) {
            continue;
        }
        $ret[] = array_combine($columns, \explode(';', $row, 100));
    }

    return $ret;
}
*/
