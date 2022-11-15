<?php
namespace vk\app\api\feed;

use vk\lib\Context;
use vk\lib\http\Request;
use function vk\app\module\user_feed\user_get_subscriptions;
use function vk\lib\cache\cache_get_all;
use function vk\lib\cache\cache_get_json;
use function vk\lib\cache\cache_key;
use function vk\lib\cache\cache_set_json;

return static function (Request $request, Context $context) {

    $key = cache_key($context, 'user_subs');

    $user_subs = cache_get_json($key, $context);
    if (empty($user_subs)) {
        $user_subs = user_get_subscriptions($context->getValue('user_id'), $context);
        if (empty($user_subs)) {
            return [];
        }
        cache_set_json($key, $user_subs, $context, 3600);
    }

    $user_keys = \array_map(static fn ($uid) => $uid . '_user_posts', $user_subs);
    $users_posts = cache_get_all($user_keys, $context);
    $feed = [];

    foreach ($user_keys as $user_key) {
        $posts = \json_decode($users_posts[$user_key], true, 255, JSON_THROW_ON_ERROR);
        $user_id = \explode('_', $user_key, 2)[0] ?? 0;
        foreach ($posts as $idx => $post) {
            $posts[$idx]['user_id'] = $user_id;
        }
        \array_unshift($feed, ...$posts);
    }

    $updated_at = \array_column($feed, 'updated_at');
    array_multisort($updated_at, SORT_DESC, $feed);

    return $feed;
};
