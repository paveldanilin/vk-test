<?php

namespace vk\app\handler;

use vk\app\message\EditPostMessage;
use vk\lib\Context;
use function vk\app\module\user_post\user_update_posts_cache;
use function vk\lib\db\db_get_conn;

final class EditPostHandler
{
    public function __invoke(EditPostMessage $message, Context $context): void
    {
        $conn = db_get_conn('post_database', $context);

        $ret = $conn->query(
            'SELECT * FROM `user_posts` WHERE ' .
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

        $stmt = $conn->prepare('UPDATE `user_posts` SET title = ?, content = ? WHERE id = ?');
        $post_id = $message->getPostId();
        $title = $message->getTitle();
        $content = $message->getContent();
        $stmt->bind_param('ssi', $title, $content, $post_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        user_update_posts_cache($message->jsonSerialize(), $context);
    }
}
