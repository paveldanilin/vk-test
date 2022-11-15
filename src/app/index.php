<?php

if (empty($_SERVER['REQUEST_URI'] ?? '')) {
    http_response_code(400);
    return;
}

require_once __DIR__ . '/../lib/lib.php';
require_once __DIR__ . '/module/module.php';
require_once __DIR__ . '/message/message.php';

use function vk\lib\http\serve_request;
use function vk\lib\http\build_request_from_globals;


serve_request(
    build_request_from_globals(),
    './api/routes.php',
    './config.php'
);
