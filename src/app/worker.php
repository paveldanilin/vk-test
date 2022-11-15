<?php

/**
 * Usage: worker.php <queue_name> <limit>
 * Examples:
 * worker.php post.create
 * worker.php post.delete 100
 */

use vk\lib\Context;
use function vk\lib\messenger\consume_messages;

require_once __DIR__ . '/../lib/lib.php';
require_once __DIR__ . '/module/module.php';
require_once __DIR__ . '/message/message.php';
require_once __DIR__ . '/handler/handler.php';

$config = include __DIR__ . '/config.php';
$host = $config['message_bus']['host'] ?? 'localhost';
$user = $config['message_bus']['user'] ?? 'guest';
$pass = $config['message_bus']['pass'] ?? 'guest';

$consumers = $config['messenger']['consumers'] ?? [];
if (empty($consumers)) {
    trigger_error('Must be defined at least one consumer', E_USER_ERROR);
}

$context = new Context(['config' => $config]);
$queue = $argv[1] ?? '';
$limit = $argv[2] ?? 50;
$retry = 15;
$retry_timeout = 15;

if (empty($queue)) {
    trigger_error('Not defined queue name', E_USER_ERROR);
}

$options = [
    'host' => $host,
    'user' => $user,
    'pass' => $pass,
    'queue_name' => $queue,
    'limit' => (int)$limit,
    'retry_timeout' => $retry_timeout,
    'retry' => $retry,
];

consume_messages($options, $context, $consumers);
