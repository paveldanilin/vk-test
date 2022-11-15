<?php
namespace vk\app\module\user_post;

use vk\lib\Context;
use function vk\lib\cache\cache_get_json;
use function vk\lib\cache\cache_key;
use function vk\lib\cache\cache_set_json;
//use function vk\lib\db\db_insert;
use function vk\lib\db\db_get_conn;
use function vk\lib\db\db_select;
use function vk\lib\util\id_create;

function cache_list_key(Context $context): string
{
    return cache_key($context, 'user_posts');
}

function cache_single_key(int $post_id, Context $context): string
{
    return cache_key($context, 'user_post_' . $post_id);
}

function user_persist_post(array $post_data, Context $context): int
{
    $id = (int)($post_data['post_id'] ?? id_create($context->getValue('user_id')));
    //$post_data['id'] = $id;
    //unset($post_data['post_id']);
    //db_insert('post_database', $context, $post_data);

    $conn = db_get_conn('post_database', $context);
    $stmt = $conn->prepare('INSERT INTO `user_posts` (`id`, `user_id`, `title`, `content`) VALUES (?,?,?,?)');
    $user_id = (int)($post_data['user_id'] ?? 0);
    $stmt->bind_param('iiss', $id, $user_id, $post_data['title'], $post_data['content']);
    if (!$stmt->execute()) {
        echo "Could not persist: ". print_r($post_data, true) . ' ' . $stmt->error . "\n";
    }
    $stmt->close();
    $conn->close();

    return $id;
}

function user_get_posts(int $user_id, int $post_id, Context $context): ?array
{
    // single
    if ($post_id > 0) {
        $cache_key = cache_single_key($post_id, $context);
        $cached_value = cache_get_json($cache_key, $context);

        if (null === $cached_value) {
            $post_data = db_select(
                'post_database',
                $context,
                'user_posts',
                ['user_id' => $user_id, 'id' => $post_id, 'deleted_at' => ['IS NULL']],
                ['title', 'content', 'updated_at'],
                [],
                1)[0] ?? [];
            if (empty($post_data)) {
                return null;
            }
            $post_data['post_id'] = $post_id;
            user_update_posts_cache($post_data, $context);
            return $post_data;
        }

        return $cached_value;
    }

    // list
    $cache_key = cache_list_key($context);
    $cached_value = cache_get_json($cache_key, $context);

    if (null === $cached_value) {
        // TODO: пагинация
        $posts = db_select(
            'post_database',
            $context,
            'user_posts',
            ['user_id' => $user_id, 'deleted_at' => ['IS NULL']],
            ['id' => 'post_id', 'title', 'content', 'updated_at'],
            ['created_at' => 'DESC'],
            100
        );
        cache_set_json($cache_key, $posts, $context);
        return $posts;
    }

    return $cached_value;
}

function user_update_posts_cache(array $post_data, Context $context): bool
{
    $user_id = $context->getValue('user_id');
    $post_id = $post_data['post_id'] ?? '';
    if (empty($post_id)) {
        return false;
    }

    $cache_data = [
        'post_id' => ($post_data['post_id'] ?? ''),
        'title' => ($post_data['title'] ?? ''),
        'content' => ($post_data['content'] ?? ''),
        'updated_at' => ($post_data['updated_at'] ?? \time())
    ];

    // last 100 posts
    $list_key = cache_list_key($context);
    $posts = db_select(
        'post_database',
        $context,
        'user_posts',
        ['user_id' => $user_id, 'deleted_at' => ['IS NULL']],
        ['id' => 'post_id', 'title', 'content', 'updated_at'],
        ['created_at' => 'DESC'],
        100
    );
    cache_set_json($list_key, $posts, $context);

    /*
    if (empty($user_posts)) {
        $user_posts = [$cache_data];
    } else {
        if (\count($user_posts) >= 100) {
            $user_posts = \array_slice($user_posts, 0, 99);
        }
        \array_unshift($user_posts, $cache_data);
    }
    cache_set_json($list_key, $user_posts, $context);*/

    // single
    $single_key = cache_single_key($post_id, $context);
    cache_set_json($single_key, $cache_data, $context);

    return true;
}
