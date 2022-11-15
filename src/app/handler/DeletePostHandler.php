<?php
namespace vk\app\handler;

use vk\app\message\DeletePostMessage;
use vk\lib\Context;
use function vk\app\module\user_post\cache_list_key;
use function vk\app\module\user_post\cache_single_key;
use function vk\lib\cache\cache_del;
use function vk\lib\cache\cache_set_json;
use function vk\lib\db\db_get_conn;
use function vk\lib\db\db_select;

final class DeletePostHandler
{
    public function __invoke(DeletePostMessage $message, Context $context): void
    {
        $conn = db_get_conn('post_database', $context);

        $ret = $conn->query(
            'SELECT id FROM `user_posts` WHERE ' .
            ' id = ' . $message->getPostId() .
            ' AND deleted_at IS NULL LIMIT 1'
        );
        if (false === $ret || $ret->num_rows === 0) {
            echo "Post not found [" . $message->getPostId() . "]\n";
            return;
        }

        $db_post = $ret->fetch_assoc();
        if ($db_post['user_id'] != $message->getUserId()) {
            echo "Cannot update\n";
            return;
        }

        $stmt = $conn->prepare('UPDATE `user_posts` SET deleted_at = ? WHERE id = ?');
        $post_id = $message->getPostId();
        $deleted_at = \time();
        $stmt->bind_param('ii', $deleted_at, $post_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // TODO: update cache
        $single_key = cache_single_key($post_id, $context);
        cache_del($single_key, $context);

        $list_key = cache_list_key($context);
        $posts = db_select(
            'post_database',
            $context,
            'user_posts',
            ['user_id' => $context->getValue('user_id'), 'deleted_at' => ['IS NULL']],
            ['id' => 'post_id', 'title', 'content', 'updated_at'],
            ['created_at' => 'DESC'],
            100
        );
        cache_set_json($list_key, $posts, $context);

        print '';
    }
}
