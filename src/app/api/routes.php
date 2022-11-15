<?php

/**
 * Route => handler mapping.
 * Format:
 * <http_method>:<path> => callable / path to file that returns callable
 */

return [
    // USER POSTS API
    'post:/api/v1/posts' => ['./api/auth.php', './api/post/add.php'],
    // /api/v1/posts?post_id=123
    'delete:/api/v1/posts' => ['./api/auth.php', './api/post/delete.php'],
    'put:/api/v1/posts' => ['./api/auth.php', './api/post/edit.php'],
    // /api/v1/posts => all
    // /api/v1/posts?post_id => by id
    'get:/api/v1/posts' => ['./api/auth.php', './api/post/get.php'],

    // USER FEED API
    'post:/api/v1/feed/subscribe' => ['./api/auth.php', './api/feed/subscribe.php'],
    'post:/api/v1/feed/unsubscribe' => ['./api/auth.php', './api/feed/unsubscribe.php'],
    'get:/api/v1/feed' => ['./api/auth.php', './api/feed/get.php'],

    // COMMON
    // [GET] /api/status
    'get:/api/status' => static function () {
        return [
            'status' => 'OK',
        ];
    },
];
