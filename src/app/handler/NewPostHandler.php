<?php
namespace vk\app\handler;

use vk\app\message\NewPostMessage;
use vk\lib\Context;
use function vk\app\module\user_post\user_persist_post;
use function vk\app\module\user_post\user_update_posts_cache;

final class NewPostHandler
{
    public function __invoke(NewPostMessage $message, Context $context): void
    {
        $post_data = $message->jsonSerialize();
        user_persist_post($post_data, $context);
        user_update_posts_cache($post_data, $context);
    }
}
