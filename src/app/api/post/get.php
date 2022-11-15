<?php
namespace vk\app\api\post;

use vk\lib\Context;
use vk\lib\http\Request;
use function vk\app\module\user_post\user_get_posts;


return static function (Request $req, Context $context) {
    $post_id = $req->getQueryParam('post_id', -1);

    $ret = user_get_posts($context->getValue('user_id'), $post_id, $context);
    if ($post_id > 0) {
        return $ret[0] ?? [];
    }
    return $ret;
};
