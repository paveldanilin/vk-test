<?php
namespace vk\app\api\post;

use vk\app\message\DeletePostMessage;
use vk\lib\Context;
use vk\lib\http\exception\HttpBadRequestException;
use vk\lib\http\exception\HttpServerException;
use vk\lib\http\Request;
use vk\lib\messenger\DurableMessage;
use function vk\lib\messenger\publish_message;


return static function (Request $req, Context $context) {

    $post_id = $req->getQueryParam('post_id', -1);
    if ($post_id < 0) {
        throw new HttpBadRequestException('Not defined `post_id`');
    }

    $del_post = new DeletePostMessage();
    $del_post->setUserId($context->getValue('user_id'));
    $del_post->setPostId($post_id);

    $msg = new DurableMessage('user.posts', $del_post);

    if (!publish_message($msg, $context)) {
        throw new HttpServerException('Could not publish message');
    }

    return '';
};
