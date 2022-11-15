<?php

const USER_ID_HEADER = 'X-User-ID';

return [
    'user_database' => [
        'user' => 'backend',
        'pass' => 'backend',
        'dbname' => 'users',
        'pool' => [
            'users_db1',
            'users_db2',
        ]
    ],
    'post_database' => [
        'user' => 'backend',
        'pass' => 'backend',
        'dbname' => 'posts',
        'pool' => [
            'post_db1',
            'post_db2',
        ]
    ],
    'memcached' => [
        'pool' => [
            'memcached1',
            'memcached2',
            'memcached3',
            'memcached4',
        ]
    ],
    'message_bus' => [
        'host' => 'rabbitmq',
        'user' => 'backend',
        'pass' => 'backend',
    ],
    'messenger' => [
        // Не работает
        'routing' => [
            \vk\app\message\NewPostMessage::class => 'post_queue',
            \vk\app\message\DeletePostMessage::class => 'post_queue',
            \vk\app\message\EditPostMessage::class => 'post_queue'
        ],
        'consumers' => [
            \vk\app\message\NewPostMessage::class => \vk\app\handler\NewPostHandler::class,
            \vk\app\message\EditPostMessage::class => \vk\app\handler\EditPostHandler::class,
            \vk\app\message\DeletePostMessage::class => \vk\app\handler\DeletePostHandler::class,
            \vk\app\message\SubscribeMessage::class => \vk\app\handler\SubscribeHandler::class,
            \vk\app\message\UnsubscribeMessage::class => \vk\app\handler\UnsubscribeHandler::class
        ]
    ]
];
